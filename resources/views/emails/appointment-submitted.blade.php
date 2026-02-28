<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Request Received</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 15px;">

                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                       style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); overflow: hidden; max-width: 600px; width: 100%;">

                    {{-- Top accent bar --}}
                    <tr>
                        <td style="background: linear-gradient(90deg, #1e3a8a 0%, #1d4ed8 100%); height: 6px;"></td>
                    </tr>

                    {{-- Header --}}
                    <tr>
                        <td style="padding: 36px 40px 20px 40px; text-align: center; border-bottom: 1px solid #e5e7eb;">
                            <h1 style="color: #111827; font-size: 22px; font-weight: 700; margin: 0; letter-spacing: -0.5px;">
                                Digos City Legal Office
                            </h1>
                            <p style="color: #6b7280; font-size: 13px; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 1px;">
                                Appointment Request Acknowledgement
                            </p>
                        </td>
                    </tr>

                    {{-- Status badge --}}
                    <tr>
                        <td style="padding: 28px 40px 0 40px; text-align: center;">
                            <div style="display: inline-block; background-color: #fef9c3; border: 1px solid #fde047; border-radius: 9999px; padding: 6px 18px;">
                                <span style="color: #854d0e; font-size: 13px; font-weight: 600;">⏳ Pending Review</span>
                            </div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 24px 40px 32px 40px;">

                            <p style="color: #374151; font-size: 16px; line-height: 1.7; margin: 0 0 16px 0;">
                                Dear <strong>{{ $clientRecord->first_name }} {{ $clientRecord->last_name }}</strong>,
                            </p>
                            <p style="color: #374151; font-size: 15px; line-height: 1.7; margin: 0 0 24px 0;">
                                We have received your appointment request. Our staff will review it and send you a
                                <strong>confirmation email</strong> once it has been approved. We aim to respond within
                                <strong>1–2 business days</strong>.
                            </p>

                            {{-- Reference number box --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 16px 20px; text-align: center;">
                                        <p style="color: #1e40af; font-size: 11px; text-transform: uppercase; font-weight: 700; margin: 0 0 4px 0; letter-spacing: 1px;">
                                            Your Reference Number
                                        </p>
                                        <p style="color: #1e3a8a; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: 2px; font-family: monospace;">
                                            {{ $appointment->reference_number }}
                                        </p>
                                        <p style="color: #3b82f6; font-size: 12px; margin: 6px 0 0 0;">
                                            Please keep this number for your records.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Appointment details --}}
                            <h2 style="color: #111827; font-size: 15px; font-weight: 700; margin: 0 0 14px 0; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                Appointment Details
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px; width: 38%; vertical-align: top;">Date:</td>
                                    <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; vertical-align: top;">
                                        {{ $appointment->start_datetime->format('F j, Y (l)') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px; vertical-align: top;">Time:</td>
                                    <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; vertical-align: top;">
                                        {{ $appointment->start_datetime->format('g:i A') }} – {{ $appointment->end_datetime->format('g:i A') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px; vertical-align: top;">Lawyer:</td>
                                    <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; vertical-align: top;">
                                        {{ $appointment->lawyer->user->name ?? 'To be assigned' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px; vertical-align: top;">Legal Category:</td>
                                    <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; vertical-align: top;">
                                        {{ $appointment->detected_services['primary'] ?? 'General Consultation' }}
                                        @if(!empty($appointment->detected_services['secondary']))
                                            <span style="color: #6b7280; font-weight: 400;"> / {{ $appointment->detected_services['secondary'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px; vertical-align: top;">Location:</td>
                                    <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600; vertical-align: top;">
                                        City Legal Office<br>
                                        <span style="color: #4b5563; font-weight: 400; font-size: 13px;">City Hall Building, Digos City</span>
                                    </td>
                                </tr>
                            </table>

                            {{-- Document checklist if available --}}
                            @if(!empty($checklist))
                            <h2 style="color: #111827; font-size: 15px; font-weight: 700; margin: 0 0 14px 0; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                Documents to Prepare
                            </h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <ul style="margin: 0; padding-left: 20px; color: #374151; font-size: 14px; line-height: 2;">
                                            @foreach($checklist as $doc)
                                                <li>
                                                    <strong>{{ $doc['item'] ?? $doc }}</strong>
                                                    @if(!empty($doc['required']) && $doc['required'])
                                                        <span style="color: #dc2626; font-size: 11px; font-weight: 600; margin-left: 4px;">REQUIRED</span>
                                                    @endif
                                                    @if(!empty($doc['description']))
                                                        <br><span style="color: #6b7280; font-size: 13px;">{{ $doc['description'] }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            {{-- What's next steps --}}
                            <h2 style="color: #111827; font-size: 15px; font-weight: 700; margin: 0 0 14px 0; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
                                What Happens Next?
                            </h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 6px 0; vertical-align: top; width: 28px;">
                                        <span style="background-color: #1d4ed8; color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700; width: 22px; height: 22px; display: inline-block; text-align: center; line-height: 22px;">1</span>
                                    </td>
                                    <td style="padding: 6px 0 6px 10px; color: #374151; font-size: 14px; line-height: 1.5;">
                                        Our staff will review your appointment request.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; vertical-align: top;">
                                        <span style="background-color: #1d4ed8; color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700; width: 22px; height: 22px; display: inline-block; text-align: center; line-height: 22px;">2</span>
                                    </td>
                                    <td style="padding: 6px 0 6px 10px; color: #374151; font-size: 14px; line-height: 1.5;">
                                        You will receive a <strong>confirmation email</strong> at <strong>{{ $clientRecord->email }}</strong> once approved.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; vertical-align: top;">
                                        <span style="background-color: #1d4ed8; color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700; width: 22px; height: 22px; display: inline-block; text-align: center; line-height: 22px;">3</span>
                                    </td>
                                    <td style="padding: 6px 0 6px 10px; color: #374151; font-size: 14px; line-height: 1.5;">
                                        If approved, please arrive <strong>10 minutes</strong> before your scheduled time with the required documents.
                                    </td>
                                </tr>
                            </table>

                            {{-- Important note --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background-color: #fefce8; border-left: 4px solid #eab308; border-radius: 4px; padding: 14px 18px;">
                                        <p style="color: #713f12; font-size: 13px; line-height: 1.6; margin: 0;">
                                            <strong>⚠ Important:</strong> Your appointment is <strong>not yet confirmed</strong>. Please do not visit our office until you receive the approval email. We will notify you within 1–2 business days.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; line-height: 1.6; margin: 0;">
                                Digos City Legal Office &nbsp;|&nbsp; City Hall Building, Digos City<br>
                                Email: <a href="mailto:legal@digoscity.gov.ph" style="color: #1d4ed8; text-decoration: none;">legal@digoscity.gov.ph</a>
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 12px 0 0 0;">
                                This is an automated message. Please do not reply to this email.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
