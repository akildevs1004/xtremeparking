 <table class="no-border" cellpadding="0" cellspacing="0" style="width:100%">
     <td class="no-border"
         style=" vertical-align: middle; text-align: center; font-size: 10px; width:180px  ">

         <table class="no-border" cellpadding="0" cellspacing="0" style="width:100%">
             <tr class="no-border">
                 <td style="font-weight: bold">
                     {{changeDateformatTime($note->created_datetime)}}

                 </td>
             </tr>
             @php
             if($note->security)
             {
             @endphp
             <tr>
                 <td style="padding:0px;">
                     <table cellpadding="0" cellspacing="0" style="width:100%;line-height:20px">
                         <tr style="border-bottom:0px solid #8f8f8f">

                             <td colspan="2">
                                 {{ $note->security->first_name   }} {{ $note->security->last_name   }}</
                                     </td>
                         </tr>
                         <tr style="border-bottom:0px solid #8f8f8f">
                            <td style="width:20px;  margin :auto;vertical-align:middle">
                                <img style=" padding-top:8px;margin :auto;vertical-align:middle"  src="{{env('BASE_PUBLIC_URL')}}/icons/email.png" width="15">
                             </td>
                             <td style="margin :auto">
                                 {{ $note->security->email? $note->security->email:'---' }}
                             </td>
                         </tr>


                         <tr style="border-bottom:0px solid #8f8f8f">
                             <td style="width:20px;  margin :auto;vertical-align:middle">
                                 <img style="padding-top:5px;margin :auto" src="{{env('BASE_PUBLIC_URL')}}/icons/phone.png" width="15">
                             </td>
                             <td>
                                 {{ $note->security->contact_number? $note->security->contact_number:'---'   }}
                             </td>
                         </tr>

                     </table>


                 </td>
             </tr>

             @php
             } @endphp
         </table>
     </td>
     <td
         style=" border-left: 1px solid #ddd;  vertical-align: middle;  text-align: center;width:70px ">
         <div
             style="  position: relative;  left: -24px; width: 1px; height: 1px; border-radius: 50%; padding: 20px; border: 1px solid #000; background-color: #fff;  display: flex; justify-content: center;  align-items: center;  ">
             <div style="text-align: center">
                 <img
                     style="   border-radius: 50%; width: 35px;  max-width: 35px;  margin-top:-16px  "
                     src="https://alarmbackend.xtremeguard.org/forwardicon.png" />
             </div>
         </div>
     </td>
     <td style="vertical-align: middle;">
         <div style="position: relative; top: 50px; left: -15px">
             <img
                 style="width: 15px"
                 src="https://alarmbackend.xtremeguard.org/alarm-notes-left-arrow.png?1=2" />
         </div>
         <div style="  margin:auto; ">
             <div
                 style=" min-height:80px; height:auto;  border: 1px solid #ddd; border-radius: 6px; padding-left: 20px;  padding-top: 20px; margin-top: -10px;  ">
                 <div style="font-weight: bold; font-size: 14px">

                     Forwarded by <span>{{ $note->security_id?"Operator":'Admin' }}</span>



                 </div>
                 <div style="padding-top: 10px; font-size: 10px">
                     <span class="bold" style="font-style:italic">Operator Notes: </span>{{ $note->notes }}
                 </div>

             </div>
         </div>

     </td>
 </table>
