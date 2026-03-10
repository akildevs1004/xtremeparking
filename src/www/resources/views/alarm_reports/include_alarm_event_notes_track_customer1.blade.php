<table style="width:100%">
    <tr>
        <td style="width:40%">
            <table style="width:100%">
                <tr>
                    <td style="width: 80px">
                        <img style="border-radius: 50%;height: 80px;min-height: 80px;width:80px;max-width: 80px;"
                            src="{{ $customerLogo }}" />
                    </td>
                    <td>
                        <div style="line-height:14px;padding-left:10px">


                            <div style="font-weight: bold">
                                <span>{{ $alarm['device']['customer']['building_name'] }}</span>
                                <span>({{ $alarm['device']['customer']['buildingtype']['name'] }})</span>
                            </div>
                            <div>{{ $alarm->device->customer->house_number ?? '---' }},
                                {{ $alarm->device->customer->street_number ?? '---' }}</div>
                            <div>{{ $alarm->device->customer->area ?? '---' }},
                                {{ $alarm->device->customer->city ?? '---' }}</div>
                            <div>


                                <table>
                                    <tr>
                                        <td><span><img style="margin :auto"
                                                    src="{{ env('BASE_PUBLIC_URL') }}/icons/email.png" width="15"
                                                    style="padding-top:5px"></span>

                                        </td>
                                        <td><span>{{ $alarm->device->customer->user->email ?? '---' }}</span></td>
                                    </tr>
                                </table>

                            </div>
                            <div>
                                <table>
                                    <tr>
                                        <td><span><img style="margin :auto"
                                                    src="{{ env('BASE_PUBLIC_URL') }}/icons/phone.png"
                                                    width="15"></span>

                                        </td>
                                        <td><span>{{ $alarm->device->customer->contact_number ?? '---' }}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        <td style="border-left:1px solid black;width:30%">
            <table style="width:100%">
                <tr>
                    <td>
                        <img style=" border-radius: 50%; width: 40px;  max-width: 40px;  "
                            src="{{ env('BASE_PUBLIC_URL') }}" />
                    </td>
                    <td>
                        <table style="width: 100%; border-collapse: collapse;line-height:20px">
                            <tr style="border-bottom: 1px solid rgb(143, 141, 141)">
                                <td>Alarm Type</td>
                                <td>{{ $alarm->alarm_type ?? '---' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgb(143, 141, 141)">
                                <td>Alarm Location</td>
                                <td> {{ $alarm->zoneData->location ?? '---' }}</td>
                            </tr>
                            <tr style="border-bottom:1px solid rgb(143, 141, 141)">
                                <td>Sensor Name</td>
                                <td>{{ $alarm->zoneData->sensor_type ?? '---' }}</td>
                            </tr>
                            <tr style="border-bottom:0px solid rgb(143, 141, 141)">
                                <td>Alarm Zone</td>
                                <td>{{ $alarm->zoneData->sensor_name ?? '---' }}</td>

                            </tr>
                        </table>
                    </td>
                </tr>
            </table>


        </td>
        <td style="border-left:1px solid black;width:30%">

            <table style="width: 100%; border-collapse: collapse;line-height:16px">
                <tr style="border-bottom: 1px solid rgb(143, 141, 141)">
                    <td>Event Id</td>
                    <td style="color:red">#{{ $alarm->id }}</td>
                </tr>
                <tr style="border-bottom: 1px solid rgb(143, 141, 141)">
                    <td>Status</td>

                    <td>
                        @if ($alarm->forwarded == true && $alarm->alarm_status == 1)
                            Forwarded
                        @elseif($alarm->alarm_status == 1)
                            Open
                        @else
                            Closed
                        @endif
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid rgb(143, 141, 141)">
                    <td>Category</td>
                    <td>
                        @if ($alarm->alarm_category == 1)
                            Critical
                        @elseif ($alarm->alarm_category == 2)
                            Medium
                        @else
                            Low
                        @endif
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid rgb(143, 141, 141)">
                    <td>Start</td>
                    <td>{{ changeDateformatTime($alarm->alarm_start_datetime) }}</td>
                </tr>
                <tr style="border-bottom: 0px solid rgb(143, 141, 141)">
                    <td>End</td>
                    <td>{{ changeDateformatTime($alarm->alarm_end_datetime) }}</td>
                </tr>
            </table>

        </td>
    </tr>
</table>
