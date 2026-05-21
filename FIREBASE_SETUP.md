# Firebase Setup Guide for PodWave

Complete guide to add Firebase authentication with "Continue with Google" option.

## Step 1: Firebase Project Setup

### 1.1 Create Firebase Project
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Add project"
3. Enter project name: `PodWave` and click "Continue"
4. Disable Google Analytics (optional) and create project
5. Wait for project to initialize

### 1.2 Register Web App
1. Click the `</>` (Web) icon to create a web app
2. App name: `PodWave Web`
3. Check "Also set up Firebase Hosting"
4. Click "Register app"
5. **COPY THE FIREBASE CONFIG** - You'll need this in Step 3

Your config will look like:
```javascript
const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project.appspot.com",
  messagingSenderId: "YOUR_SENDER_ID",
  appId: "1:YOUR_APP_ID:web:YOUR_WEB_ID"
};
```

## Step 2: Configure Google OAuth

### 2.1 Setup Google OAuth Consent Screen
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your Firebase project
3. Navigate to "APIs & Services" → "OAuth consent screen"
4. Choose "External" user type and click "Create"
5. Fill in required fields:
   - App name: `PodWave`
   - User support email: your-email@example.com
   - Developer contact info: your-email@example.com
6. Click "Save and Continue"
7. Skip scopes and save
8. Add your test email in "Test users"

### 2.2 Create OAuth 2.0 Credentials
1. Navigate to "Credentials" page
2. Click "Create Credentials" → "OAuth 2.0 Client IDs"
3. Choose "Web application"
4. Name: `PodWave Web Client`
5. Add Authorized JavaScript origins:
   - `http://localhost:8000`
   - `http://localhost:3000`
   - `https://yourwebsite.com` (production)
6. Add Authorized redirect URIs:
   - `http://localhost:8000/auth/google/callback`
   - `http://localhost:3000/auth/google/callback`
   - `https://yourwebsite.com/auth/google/callback` (production)
7. Click "Create"
8. **COPY YOUR CLIENT ID AND SECRET**

### 2.3 Enable Google Sign-In in Firebase
1. Go back to Firebase Console
2. Navigate to "Authentication" → "Sign-in method"
3. Click on "Google"
4. Enable it and select your project from the dropdown
5. Click "Save"

## Step 3: Install Required Packages

```bash
# Install Firebase and Google OAuth packages
composer require laravel/socialite
npm install firebase
npm install @react-oauth/google
```

## Step 4: Create Laravel Configuration

### 4.1 Update config/services.php
Add this before the last closing brace:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/auth/google/callback'),
],

'firebase' => [
    'api_key' => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id' => env('FIREBASE_APP_ID'),
],
```

### 4.2 Update .env file
Add these variables from your Firebase and Google OAuth setup:

```env
# Google OAuth
GOOGLE_CLIENT_ID=YOUR_GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET=YOUR_GOOGLE_CLIENT_SECRET
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Firebase Config
FIREBASE_API_KEY=YOUR_FIREBASE_API_KEY
FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_STORAGE_BUCKET=your-project.appspot.com
FIREBASE_MESSAGING_SENDER_ID=YOUR_SENDER_ID
FIREBASE_APP_ID=1:YOUR_APP_ID:web:YOUR_WEB_ID
```

## Step 5: Create Laravel Routes

### 5.1 Add routes to routes/web.php

```php
// Google OAuth routes
Route::get('/auth/google', [App\Http\Controllers\Auth\OAuthController::class, 'redirectToGoogle'])
    ->name('auth.google');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\OAuthController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');
```

## Step 6: Create OAuthController

Create file: `app/Http/Controllers/Auth/OAuthController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    /**
     * Redirect to Google OAuth provider
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();

            // Check if user exists by email
            $existingUser = User::where('email', $user->getEmail())->first();

            if ($existingUser) {
                // Update Google ID if not set
                if (!$existingUser->google_id) {
                    $existingUser->update([
                        'google_id' => $user->getId(),
                    ]);
                }
                Auth::login($existingUser, true);
            } else {
                // Create new user
                $newUser = User::create([
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'google_id' => $user->getId(),
                    'avatar' => $user->getAvatar(),
                    'password' => bcrypt(Str::random(16)), // Random password
                    'role' => 'listener', // Default role
                    'username' => Str::slug($user->getName()) . '-' . Str::random(4),
                    'email_verified_at' => now(),
                ]);

                Auth::login($newUser, true);
            }

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'Failed to authenticate with Google']);
        }
    }
}
```

## Step 7: Update User Model

Add these properties to `app/Models/User.php`:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'google_id',
    'avatar',
    'role',
    'username',
    'bio',
    'website',
    'social_links',
    'is_banned',
    'ban_reason',
    'email_verified_at',
];

protected $hidden = [
    'password',
    'remember_token',
];

protected $casts = [
    'email_verified_at' => 'datetime',
    'social_links' => 'array',
    'is_banned' => 'boolean',
];
```

## Step 8: Create Database Migration

Create migration: `database/migrations/2024_01_01_000000_add_google_id_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });
    }
};
```

Run migration:
```bash
php artisan migrate
```

## Step 9: Update Login View

Add this to `resources/views/auth/login.blade.php` in your login form:

```html
<!-- Google Sign-In Button -->
<div class="mt-6">
    <a href="{{ route('auth.google') }}" 
       class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
        <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#4285F4" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        <span class="ml-2">Continue with Google</span>
    </a>
</div>

<p class="text-center text-sm text-gray-600 mt-4">
    or
</p>
```

## Step 10: Setup Frontend Firebase (Optional - for Client-side)

If you want to use Firebase SDK in your JavaScript frontend:

### 10.1 Create Firebase config file
Create `resources/js/firebase.js`:

```javascript
import { initializeApp } from 'firebase/app';
import { getAuth, connectAuthEmulator } from 'firebase/auth';

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID,
};

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);

// Uncomment for local development
// connectAuthEmulator(auth, "http://localhost:9099");
```

### 10.2 Update .env for Vite
Add to `.env`:
```env
VITE_FIREBASE_API_KEY=YOUR_FIREBASE_API_KEY
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=YOUR_SENDER_ID
VITE_FIREBASE_APP_ID=1:YOUR_APP_ID:web:YOUR_WEB_ID
```

## Step 11: Testing

1. Start your development server:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

2. Go to http://localhost:8000/login

3. Click "Continue with Google"

4. Sign in with your Google account

5. You should be redirected to dashboard

## Troubleshooting

### Issue: "Redirect URI mismatch"
- Make sure your redirect URI in Google OAuth settings matches exactly

### Issue: "Invalid client"
- Verify your GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in .env

### Issue: "User creation failed"
- Check database migrations are run: `php artisan migrate`

### Issue: "localhost not recognized"
- Make sure you added `http://localhost:8000` to OAuth authorized origins

## Security Best Practices

1. ✅ Never commit `.env` file with real credentials
2. ✅ Use environment variables for all sensitive data
3. ✅ Keep Google Client Secret secure (never expose in frontend)
4. ✅ Validate email verification for OAuth users
5. ✅ Add CSRF protection to all forms
6. ✅ Use HTTPS in production

## Production Deployment

When deploying to production:

1. Update redirect URIs to your production domain
2. Add production domain to OAuth settings in Google Console
3. Update `APP_URL` in `.env` to production domain
4. Update Firebase database rules for security
5. Enable additional security measures (2FA, rate limiting, etc.)
