<?php

namespace App\Domain\User;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;
use App\Core\Security\LoginThrottle;

/**
 * Authentication Controller
 * Maneja login, registro y logout
 */
class AuthController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Show login form
     */
    public function showLogin(): Response
    {
        return view('frontend.user.login');
    }

    /**
     * Process login
     */
    public function login(Request $request): Response
    {
        $email = $request->post('email');
        $password = $request->post('password');
        $ip = $request->ip();

        $throttle = new LoginThrottle($this->db);
        if ($throttle->tooManyAttempts($email ?? '', $ip)) {
            $minutes = $throttle->minutesUntilRetry($email ?? '', $ip);
            flash('error', "Demasiados intentos fallidos. Intenta de nuevo en {$minutes} minuto(s).");
            return redirect('/account/login');
        }

        // Validate
        if (!$email || !$password) {
            flash('error', 'Email and password are required');
            return redirect('/account/login');
        }

        // Find user
        $user = $this->db->fetchOne(
            'SELECT * FROM users WHERE email = ? AND status = "active"',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $throttle->recordFailure($email ?? '', $ip);
            flash('error', 'Invalid credentials');
            return redirect('/account/login');
        }

        // Set session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role_id' => $user['role_id']
        ];

        // Update last login
        $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $request->ip()
        ], 'id = ?', [$user['id']]);

        $this->mergeGuestCartIntoUser($user['id']);

        // Dispatch event
        event('user.login', $user);

        // Redirect to intended URL or home
        $redirect = $_SESSION['intended_url'] ?? '/';
        unset($_SESSION['intended_url']);

        flash('success', 'Welcome back, ' . $user['first_name'] . '!');
        return redirect($redirect);
    }

    /**
     * Show registration form
     */
    public function showRegister(): Response
    {
        return view('frontend.user.register');
    }

    /**
     * Process registration
     */
    public function register(Request $request): Response
    {
        $data = $request->only(['first_name', 'last_name', 'email', 'password', 'password_confirmation']);

        // Validate
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }
        
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        if ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }

        // Check if email exists
        $existingUser = $this->db->fetchOne(
            'SELECT id FROM users WHERE email = ?',
            [$data['email']]
        );
        
        if ($existingUser) {
            $errors['email'] = 'Email already registered';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return redirect('/account/register');
        }

        // Get default customer role
        $roleId = $this->db->fetchOne(
            'SELECT id FROM roles WHERE name = "customer"'
        )['id'] ?? 1;

        // Create user
        $userId = $this->db->insert('users', [
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'role_id' => $roleId,
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Dispatch event
        event('user.registered', ['id' => $userId, 'email' => $data['email']]);

        // Auto-login
        $_SESSION['user'] = [
            'id' => $userId,
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'role_id' => $roleId
        ];

        $this->mergeGuestCartIntoUser($userId);

        flash('success', 'Registration successful! Welcome to our store.');
        return redirect('/');
    }

    /**
     * Logout
     */
    public function logout(): Response
    {
        event('user.logout', $_SESSION['user'] ?? null);

        unset($_SESSION['user']);
        session_destroy();

        flash('success', 'You have been logged out');
        return redirect('/');
    }

    /**
     * Reassign the anonymous session cart to the now-authenticated user, so
     * items added before logging in survive into checkout (which only ever
     * looks up carts by user_id).
     */
    private function mergeGuestCartIntoUser(int $userId): void
    {
        $guestCart = $this->db->fetchOne(
            'SELECT * FROM carts WHERE session_id = ? AND user_id IS NULL',
            [session_id()]
        );

        if (!$guestCart) {
            return;
        }

        $userCart = $this->db->fetchOne('SELECT * FROM carts WHERE user_id = ?', [$userId]);

        if ($userCart) {
            $this->db->query(
                'UPDATE cart_items SET cart_id = ? WHERE cart_id = ?',
                [$userCart['id'], $guestCart['id']]
            );
            $this->db->delete('carts', 'id = ?', [$guestCart['id']]);
        } else {
            $this->db->update('carts', ['user_id' => $userId], 'id = ?', [$guestCart['id']]);
        }
    }
}
