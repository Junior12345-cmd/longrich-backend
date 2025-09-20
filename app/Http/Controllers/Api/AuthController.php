<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Models\User;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as RulesPassword;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => ['required', /*'confirmed',*/ RulesPassword::defaults(), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'status'    => 'inactive',
        ]);

        // Générer un lien signé pour la vérification
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Envoyer l'email
        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

        return response()->json([
            'message' => 'User registered. Please check your email for verification link.'
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email not verified'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Logged out']);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if(! hash_equals((string)$hash, sha1($user->getEmailForVerification()))){
            return response()->json(['message'=>'Invalid verification link'],403);
        }

        if($user->hasVerifiedEmail()){
            return response()->json(['message'=>'Email already verified']);
        }

        $user->markEmailAsVerified();
        $user->status = 'active';
        $user->save();

        return response()->json(['message'=>'Email verified successfully']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email'=>'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message'=>'If this email exists, a reset link has been sent.']);
        }

        // Générer un token
        $token = Str::random(60);

        // Sauvegarder le token dans la table password_resets
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email'=>$user->email],
            ['token'=>$token,'created_at'=>now()]
        );

        // Envoyer l'email
        Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));

        return response()->json(['message'=>'Password reset link sent.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required','confirmed', RulesPassword::defaults(), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/']
        ]);

        // Vérifier si le token existe
        $record = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->where('token', $request->token)
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid token or email'], 400);
        }

        // Modifier directement le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token après utilisation
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successful']);
    }

}
