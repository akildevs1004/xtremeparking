<div id="email" style="width:700px; margin:auto; background:white;">
    @php
        $event_backgroundcolor = 'red';
        if ($alarm['alarm_category_id'] == 2) {
            $event_backgroundcolor = 'orange';
        } elseif ($alarm['alarm_category_id'] == 3) {
            $event_backgroundcolor = '#00A4BD';
        }
    @endphp

    <table role="presentation" border="1" cellspacing="0" cellpadding="0" style="width:100%; border-collapse:collapse;">
        <tr>
            <td bgcolor="{{ $event_backgroundcolor }}" align="center" style="color: white; padding: 10px;">
                <h2 style="margin: 0; font-size: 20px;">#{{ $alarm['id'] }} {{ $alarm['alarm_type'] }} Notification at
                    {{ $alarm['device']['customer']['building_name'] }}. Time {{ $alarm['alarm_start_datetime'] }}</h2>
            </td>
        </tr>
    </table>

    <table role="presentation" border="1" cellspacing="0" cellpadding="0"
        style="width:100%; border-collapse:collapse; margin-top: 10px;">
        <tr>
            <td style="padding: 10px;">
                <h2 style="margin: 0;">Hello {{ $name }},</h2>
                <table role="presentation" border="1" cellspacing="0" cellpadding="0"
                    style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding: 5px;">
                            <table style="width:100%;">
                                <tr>
                                    <td style="font-weight:bold">Event ID</td>
                                    <td>: #{{ $alarm['id'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Priority</td>
                                    <td>: {{ $alarm['category']['name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Event Type</td>
                                    <td>: {{ $alarm['alarm_type'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Event Time</td>
                                    <td>: {{ $alarm['alarm_start_datetime'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Closed Time</td>
                                    <td>: {{ $alarm['alarm_end_datetime'] ?? '---' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Resolved Time</td>
                                    <td>: {{ $alarm['response_minutes'] ?? '---' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table role="presentation" border="1" cellspacing="0" cellpadding="0"
                    style="width:100%; border-collapse:collapse; margin-top: 10px;">
                    <tr>
                        <td style="padding: 5px;">
                            <table style="width:100%;">
                                <tr>
                                    <td style="font-weight:bold">Building Name</td>
                                    <td>{{ $alarm['customer']['building_name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Property Type</td>
                                    <td>{{ $alarm['customer']['buildingtype']['name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Address</td>
                                    <td>{{ $alarm['customer']['house_number'] }},
                                        {{ $alarm['customer']['street_number'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Area</td>
                                    <td>{{ $alarm['customer']['area'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">City</td>
                                    <td>{{ $alarm['customer']['city'] }}, {{ $alarm['customer']['state'] }},
                                        {{ $alarm['customer']['country'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Landmark</td>
                                    <td>{{ $alarm['customer']['landmark'] }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold">Google Map Link</td>
                                    <td><a
                                            href="https://maps.google.com/?q={{ $alarm['customer']['latitude'] }},{{ $alarm['customer']['longitude'] }}">Map
                                            Location</a></td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 5px;text-align:center">
                            {{-- <img src="{{ $alarm['customer']['profile_picture'] }}" style="margin:auto;width:100%; max-width:300px;" alt="Building Image"> --}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br />

    <table role="presentation" border="1" cellspacing="0" cellpadding="0"
        style="width:100%; border-collapse:collapse; margin-top: 10px;">
        <tr>
            <td colspan="2">
                <h4>Contacts List</h4>
            </td>
        </tr>
        <tr>
            @foreach ($alarm['customer']['contacts'] as $index => $contact)
                @if ($index % 2 == 0 && $index != 0)
        </tr>
        <tr>
            @endif
            <td style="width:50%; padding: 0; vertical-align:top;height:180px">
                <table role="presentation" border="1" cellspacing="0" cellpadding="0"
                    style="width:100%; border-collapse:collapse; margin-bottom: 10px;">
                    <tr>
                        <td style="font-weight:bold">{{ $index + 1 }}: {{ $contact['address_type'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $contact['first_name'] }} {{ $contact['last_name'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $contact['phone1'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $contact['phone2'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $contact['office_phone'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $contact['email'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $contact['whatsapp'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $contact['distance'] }}</td>
                    </tr>
                </table>
            </td>
            @endforeach
        </tr>
    </table>

    <!-- Footer -->
    <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
        <tr>
            <td bgcolor="#F5F8FA" style="padding: 30px; text-align:left; width:50%;">
                @if (env('APP_ENV') !== 'local')
                    <img src="{{ $alarm['device']['company']['logo'] }}"
                        style="width:100px; max-width:150px; max-height:60px;" alt="Company Logo">
                @else
                    <img src="{{ getcwd() . '/' . $alarm['device']['company']['logo_raw'] }}"
                        style="width:100px; max-width:150px; max-height:60px;" alt="Company Logo">
                @endif
            </td>
            <td bgcolor="#F5F8FA" style="padding: 30px; text-align:right; width:50%;">
                <p style="margin:0; font-size:16px; line-height:24px; color: #99ACC2;">Alarm Control Panel</p>
            </td>
        </tr>
    </table>
</div>
