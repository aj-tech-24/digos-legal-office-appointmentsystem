<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 15px;">
                
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden;">
                    
                    <tr>
                        <td style="background-color: #15803d; height: 6px;"></td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center; border-bottom: 1px solid #e5e7eb;">
                            <h1 style="color: #111827; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;">Digos City Legal Office</h1>
                            <p style="color: #6b7280; font-size: 14px; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 1px;">Appointment Confirmation</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px 40px;">
                            
                            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Dear <strong>{{ $appointment->clientRecord->first_name }} {{ $appointment->clientRecord->last_name }}</strong>,
                            </p>
                            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                We are pleased to inform you that your appointment request has been <strong style="color: #15803d;">OFFICIALLY CONFIRMED</strong>.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="color: #166534; font-size: 12px; text-transform: uppercase; margin: 0 0 4px 0; font-weight: 600;">Reference Number</p>
                                        <p style="color: #14532d; font-size: 20px; font-weight: 700; margin: 0; letter-spacing: 1px; font-family: monospace;">{{ $appointment->reference_number }}</p>
                                    </td>
                                </tr>
                            </table>

                            <h2 style="color: #111827; font-size: 16px; font-weight: 700; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">Appointment Details</h2>
                            
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px; width: 40%; vertical-align: top;">Date & Time:</td>
                                    <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; vertical-align: top;">
                                        {{ $appointment->start_datetime->format('F j, Y (l)') }}<br>
                                        <span style="color: #4b5563; font-weight: 400;">{{ $appointment->start_datetime->format('g:i A') }} – {{ $appointment->end_datetime->format('g:i A') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px; width: 40%; vertical-align: top;">Assigned Lawyer:</td>
                                    <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; vertical-align: top;">
                                        {{ $appointment->lawyer->user->name ?? 'Assigned Lawyer' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px; width: 40%; vertical-align: top;">Location:</td>
                                    <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; vertical-align: top;">
                                        City Legal Office<br>
                                        <span style="color: #4b5563; font-weight: 400; font-size: 13px;">City Hall Building, Digos City</span>
                                    </td>
                                </tr>
                            </table>

                            {{-- Requirements Section --}}
                            @if(!empty($requirements))
                                <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 4px; padding: 20px; margin-bottom: 30px;">
                                    <p style="color: #991b1b; font-size: 13px; font-weight: 700; text-transform: uppercase; margin: 0 0 10px 0;">Required Documents to Bring</p>
                                    <ul style="margin: 0; padding-left: 20px; color: #374151; font-size: 14px; line-height: 1.6;">
                                        @foreach($requirements as $req)
                                            <li style="margin-bottom: 5px;">{{ $req }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Instructions Section --}}
                            @if(!empty($instructions))
                                <div style="background-color: #eff6ff; border-left: 4px solid #1e40af; padding: 15px; margin-bottom: 30px;">
                                    <p style="color: #1e3a8a; font-size: 12px; font-weight: 700; text-transform: uppercase; margin: 0 0 5px 0;">Important Instruction from Lawyer:</p>
                                    <p style="color: #1e40af; font-size: 14px; margin: 0;">{{ $instructions }}</p>
                                </div>
                            @endif

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 20px;">
                                <tr>
                                    <td style="background-color: #fffbeb; padding: 15px; border-radius: 4px; border: 1px solid #fde68a;">
                                        <p style="color: #92400e; font-size: 13px; line-height: 1.5; margin: 0; text-align: center;">
                                            <strong>REMINDER:</strong> Please arrive at least 10 minutes before your scheduled time. Present this email or your reference number upon arrival.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; line-height: 1.5; margin: 0;">
                                Digos City Legal Office<br>
                                Contact: (082) XXX-XXXX
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 15px 0 0 0;">
                                This is an automated notification.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>