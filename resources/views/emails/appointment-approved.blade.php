<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f0f4f8;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);">

                    <tr>
                        <td style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0ea5e9 100%); padding: 40px 40px 30px 40px; text-align: center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                <tr>
                                    <td style="background-color: rgba(255,255,255,0.2); border-radius: 50%; width: 72px; height: 72px; text-align: center; vertical-align: middle;">
                                        <span style="font-size: 36px; color: #ffffff;">&#10003;</span>
                                    </td>
                                </tr>
                            </table>
                            <h1 style="color: #ffffff; font-size: 26px; font-weight: 700; margin: 20px 0 8px 0; letter-spacing: -0.5px;">Appointment Confirmed!</h1>
                            <p style="color: rgba(255,255,255,0.85); font-size: 15px; margin: 0; line-height: 1.5;">Your appointment has been approved and confirmed</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px 40px 0 40px;">
                            <p style="color: #334155; font-size: 16px; line-height: 1.6; margin: 0;">
                                Dear <strong style="color: #1e293b;">{{ $clientRecord->full_name }}</strong>,
                            </p>
                            <p style="color: #64748b; font-size: 15px; line-height: 1.6; margin: 12px 0 0 0;">
                                Great news! Your appointment with the <strong>Digos City Legal Office</strong> has been reviewed and approved. Please see your confirmed appointment details below.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px 40px 0 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); border-radius: 12px; padding: 16px 20px; text-align: center; border: 1px solid #c7d2fe;">
                                        <p style="color: #6366f1; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; margin: 0 0 4px 0;">Reference Number</p>
                                        <p style="color: #312e81; font-size: 22px; font-weight: 700; margin: 0; letter-spacing: 1px;">{{ $appointment->reference_number }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px 40px 0 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                                <tr>
                                    <td style="background-color: #1e40af; padding: 12px 20px;">
                                        <p style="color: #ffffff; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin: 0;">📋 Appointment Details</p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 16px 20px 0 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <span style="font-size: 20px;">📅</span>
                                                </td>
                                                <td valign="top">
                                                    <p style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Date</p>
                                                    <p style="color: #1e293b; font-size: 15px; font-weight: 600; margin: 4px 0 0 0;">{{ $appointment->start_datetime->format('l, F j, Y') }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 14px 20px 0 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <span style="font-size: 20px;">🕐</span>
                                                </td>
                                                <td valign="top">
                                                    <p style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Time</p>
                                                    <p style="color: #1e293b; font-size: 15px; font-weight: 600; margin: 4px 0 0 0;">{{ $appointment->start_datetime->format('g:i A') }} – {{ $appointment->end_datetime->format('g:i A') }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 14px 20px 0 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <span style="font-size: 20px;">⏱️</span>
                                                </td>
                                                <td valign="top">
                                                    <p style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Estimated Duration</p>
                                                    <p style="color: #1e293b; font-size: 15px; font-weight: 600; margin: 4px 0 0 0;">{{ $appointment->estimated_duration_minutes ?? 30 }} minutes</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 14px 20px 0 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <span style="font-size: 20px;">⚖️</span>
                                                </td>
                                                <td valign="top">
                                                    <p style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Assigned Lawyer</p>
                                                    <p style="color: #1e293b; font-size: 15px; font-weight: 600; margin: 4px 0 0 0;">{{ $lawyer->user->name ?? 'To be assigned' }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                @if(count($services) > 0)
                                <tr>
                                    <td style="padding: 14px 20px 0 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <span style="font-size: 20px;">📌</span>
                                                </td>
                                                <td valign="top">
                                                    <p style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Services</p>
                                                    <p style="color: #1e293b; font-size: 15px; font-weight: 600; margin: 4px 0 0 0;">{{ implode(', ', $services) }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endif

                                <tr>
                                    <td style="padding: 16px 20px 20px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <span style="font-size: 20px;">✅</span>
                                                </td>
                                                <td valign="top">
                                                    <p style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Status</p>
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 6px;">
                                                        <tr>
                                                            <td style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                                                                Confirmed
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px 40px 0 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fffbeb; border-radius: 12px; border: 1px solid #fde68a; overflow: hidden;">
                                <tr>
                                    <td style="background-color: #d97706; padding: 12px 20px;">
                                        <p style="color: #ffffff; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin: 0;">📁 What to Bring</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px 20px 20px;">
                                        @if(count($documents) > 0)
                                            @foreach($documents as $doc)
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 8px;">
                                                <tr>
                                                    <td width="28" valign="top" style="padding-top: 2px;">
                                                        <span style="display: inline-block; width: 20px; height: 20px; background-color: #fef3c7; border-radius: 50%; text-align: center; line-height: 20px; font-size: 11px; color: #b45309;">✓</span>
                                                    </td>
                                                    <td>
                                                        <p style="color: #78350f; font-size: 14px; margin: 0; line-height: 1.5;">{{ $doc }}</p>
                                                    </td>
                                                </tr>
                                            </table>
                                            @endforeach
                                        @else
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 8px;">
                                                <tr>
                                                    <td width="28" valign="top" style="padding-top: 2px;">
                                                        <span style="display: inline-block; width: 20px; height: 20px; background-color: #fef3c7; border-radius: 50%; text-align: center; line-height: 20px; font-size: 11px; color: #b45309;">✓</span>
                                                    </td>
                                                    <td>
                                                        <p style="color: #78350f; font-size: 14px; margin: 0; line-height: 1.5;">Valid Government ID</p>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tr>
                                                    <td width="28" valign="top" style="padding-top: 2px;">
                                                        <span style="display: inline-block; width: 20px; height: 20px; background-color: #fef3c7; border-radius: 50%; text-align: center; line-height: 20px; font-size: 11px; color: #b45309;">✓</span>
                                                    </td>
                                                    <td>
                                                        <p style="color: #78350f; font-size: 14px; margin: 0; line-height: 1.5;">Any relevant documents related to your case</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px 40px 0 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #eff6ff; border-radius: 12px; border: 1px solid #bfdbfe; overflow: hidden;">
                                <tr>
                                    <td style="background-color: #2563eb; padding: 12px 20px;">
                                        <p style="color: #ffffff; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin: 0;">💡 Important Reminders</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px 20px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 10px;">
                                            <tr>
                                                <td width="28" valign="top" style="padding-top: 2px;">
                                                    <span style="display: inline-block; width: 20px; height: 20px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; text-align: center; line-height: 20px; font-size: 11px; color: #fff; font-weight: 700;">1</span>
                                                </td>
                                                <td>
                                                    <p style="color: #1e40af; font-size: 14px; margin: 0; line-height: 1.5;">Please arrive at least <strong>10 minutes</strong> before your scheduled time.</p>
                                                </td>
                                            </tr>
                                        </table>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 10px;">
                                            <tr>
                                                <td width="28" valign="top" style="padding-top: 2px;">
                                                    <span style="display: inline-block; width: 20px; height: 20px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; text-align: center; line-height: 20px; font-size: 11px; color: #fff; font-weight: 700;">2</span>
                                                </td>
                                                <td>
                                                    <p style="color: #1e40af; font-size: 14px; margin: 0; line-height: 1.5;">If you need to cancel or reschedule, please contact us at least <strong>24 hours</strong> in advance.</p>
                                                </td>
                                            </tr>
                                        </table>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="28" valign="top" style="padding-top: 2px;">
                                                    <span style="display: inline-block; width: 20px; height: 20px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; text-align: center; line-height: 20px; font-size: 11px; color: #fff; font-weight: 700;">3</span>
                                                </td>
                                                <td>
                                                    <p style="color: #1e40af; font-size: 14px; margin: 0; line-height: 1.5;">Bring a face mask and observe health protocols.</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px 40px 0 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f0fdf4; border-radius: 12px; border: 1px solid #bbf7d0; overflow: hidden;">
                                <tr>
                                    <td style="background-color: #16a34a; padding: 12px 20px;">
                                        <p style="color: #ffffff; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin: 0;">📍 Office Location</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px 20px 20px;">
                                        <p style="color: #14532d; font-size: 15px; font-weight: 700; margin: 0;">Digos City Legal Office</p>
                                        <p style="color: #166534; font-size: 14px; margin: 6px 0 0 0; line-height: 1.5;">City Hall Building, Digos City,<br>Davao del Sur</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px 40px 0 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px solid #e2e8f0; padding-top: 24px;">
                                <tr>
                                    <td>
                                        <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 0;">
                                            If you have any questions or concerns, please don't hesitate to contact us at <strong style="color: #1e40af;">(082) XXX-XXXX</strong>.
                                        </p>
                                        <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 16px 0 0 0;">
                                            Thank you for choosing the Digos City Legal Office. We look forward to assisting you.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px 40px 40px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border-radius: 12px; padding: 24px; text-align: center;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="color: #f1f5f9; font-size: 15px; font-weight: 700; margin: 0;">Digos City Legal Office</p>
                                        <p style="color: #94a3b8; font-size: 12px; margin: 8px 0 0 0; line-height: 1.5;">City Hall Building, Digos City, Davao del Sur</p>
                                        <p style="color: #94a3b8; font-size: 12px; margin: 4px 0 0 0;">Phone: (082) XXX-XXXX</p>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin-top: 16px;">
                                            <tr>
                                                <td style="border-top: 1px solid #475569; padding-top: 16px;">
                                                    <p style="color: #64748b; font-size: 11px; margin: 0;">This is an automated email. Please do not reply directly to this message.</p>
                                                    <p style="color: #64748b; font-size: 11px; margin: 4px 0 0 0;">&copy; {{ date('Y') }} Digos City Legal Office. All rights reserved.</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>