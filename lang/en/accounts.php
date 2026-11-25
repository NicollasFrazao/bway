<?php

declare(strict_types=1);

return [
    'title' => [
        'register' => 'Create an account',
        'login' => 'Sign in',
        'forgot' => 'Forgot your password?',
        'reset' => 'Choose a new password',
    ],
    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Password again',
        'remember' => 'Keep me signed in',
    ],
    'actions' => [
        'register' => 'Create account',
        'login' => 'Sign in',
        'logout' => 'Sign out',
        'send_link' => 'Send the link',
        'reset' => 'Save the new password',
    ],
    'links' => [
        'forgot' => 'Forgot your password?',
        'no_account' => 'No account yet? Create one.',
        'has_account' => 'Have an account? Sign in.',
    ],
    'status' => [
        'reset_link' => 'If an account uses this email, a link to reset the password was sent to it.',
        'password_reset' => 'Your password was changed. Sign in with the new one.',
    ],
    'errors' => [
        'credentials' => 'The email or the password is wrong.',
        'throttled' => 'Too many attempts. Try again in :seconds seconds.',
        'taken' => 'An account with this email exists already.',
        'reset_link' => 'This link is invalid or has expired. Ask for a new one.',
        'invalid' => [
            'name' => 'Type a name of up to 100 characters.',
            'email' => 'Type a valid email address.',
            'password' => 'The password needs at least 8 characters and at most 72 bytes.',
        ],
    ],
];
