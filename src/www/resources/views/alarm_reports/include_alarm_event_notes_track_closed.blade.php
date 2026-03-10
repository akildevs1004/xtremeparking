<table cellpadding="0" cellspacing="0" style="height:150px">
    <td
        style="  vertical-align: middle; text-align: center; font-size: 10px;width:180px;color:red ">
        {{changeDateformatTime($alarm->alarm_end_datetime)}}
    </td>
    <td
        style=" border-left: 1px solid #ddd; vertical-align: middle; text-align: center;width:70px  ">
        <div
            style="  position: relative; left: -24px; width: 1px; height: 1px; border-radius: 50%; padding: 18px; border: 1px solid #000;   background-color: #fff; display: flex; justify-content: center; align-items: center; ">
            <div style="text-align: center">
                <div
                    style="  position: relative; width: 1px; height: 1px; border-radius: 50%; padding: 12px; border: 1px solid #FFF; background-color:  red; display: flex; justify-content: center; align-items: center;  margin-top:-13px;  margin-left:-13px;
                      ">

                </div>
            </div>
    </td>
    <td style="vertical-align: middle;height:140px; ">
        <div style="position: relative; top: 50px; left: -15px">
            <img
                style="width: 15px"
                src="https://alarmbackend.xtremeguard.org/alarm-notes-left-arrow.png?1=2" />
        </div>
        <div style="  margin:auto; ">

            <div
                style=" border: 1px solid #ddd; border-radius: 6px; padding-left: 20px; padding-top: 20px;padding-right: 20px; margin-top: -10px;  height:auto ">
                <div style="font-weight: bold; font-size: 14px">
                    Alarm Event Closed at {{changeDateformatTime($alarm->alarm_end_datetime)}}
                </div>
                <div style="padding-top: 10px; font-size: 10px">
                    @if($alarm->alarm_end_manually == 1)
                    <div>
                        Operator Verified PIN with {{ $alarm->pin_verified_by }} -

                        {{
                        $alarm->pinverifiedby
                          ? $alarm->pinverifiedby->first_name .
                            " " .
                            $alarm->pinverifiedby->last_name
                          : "---"
                      }}
                    </div>
                    @else

                    Auto Closed by Disarm Event
                    @endif
                </div>

                <div style="height:30px"> </div>
            </div>


        </div>
    </td>
</table>