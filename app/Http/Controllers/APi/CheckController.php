<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



class CheckController extends Controller
{


    public function verifyEmail(Request $request)
    {
        $email = $request->input('email');
        $domain = substr(strrchr($email, "@"), 1);  // Extract domain part

        // Check DNS for MX record of the domain
        if (!checkdnsrr($domain, 'MX')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email domain.',
            ], 400);
        }

        $mail = new PHPMailer(true);

        // try {
            // Set SMTP details
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST'); // SMTP server address
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME'); // Your email account username
            $mail->Password = env('MAIL_PASSWORD'); // Your email account password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // Use port 587 for STARTTLS, or 465 for SSL

            // Set the sender and recipient
            $mail->setFrom(env('MAIL_USERNAME'), 'Your App Name');
            $mail->addAddress($email); // Email to verify

            // Set the subject and body
            $mail->Subject = 'Email Verification Test';
            $mail->isHTML(true); // Set email format to HTML
            $mail->Body = '<h1>This is a test email to verify if the email address is valid.</h1>';

            // Send email
            if ($mail->send()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Email is valid and the verification email has been sent.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to send email: ' . $mail->ErrorInfo,
                ], 500);
            }

        // } catch (Exception $e) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Email verification failed: ' . $mail->ErrorInfo,
        //     ], 500);
        // }
    }



public function check(Request $request)
{
    // Step 1: Basic validation (format)
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    // If the email format is invalid
    if ($validator->fails()) {
        throw new ValidationException($validator);
    }

    // Step 2: Extract domain from the email
    $email = $request->input('email');
    $domain = substr(strrchr($email, "@"), 1);  // Extracts domain after '@'

    return checkdnsrr($domain, 'MX');
    // Step 3: Check if the domain has valid MX records (mail exchange records)
    if (!checkdnsrr($domain, 'MX')) {

        // Add custom error to the validation message bag for the email field
        $validator->getMessageBag()->add('email', 'The email domain is invalid or does not exist.');

        // Throw a validation exception with the custom error
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors(),  // Return errors as JSON response
        ], 422));
    }

    // Step 4: If domain is valid, return success response
    return response()->json(['message' => 'Email is valid and domain exists.'], 200);
}




}
