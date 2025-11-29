 <table cellpadding="0" cellspacing="0" style="height:140px;width:100%;border:0px solid red;line-height:18px">
     <td
         style="  vertical-align: middle;  text-align: left; font-size: 10px;width:180px;  ">

         <table cellpadding="0" cellspacing="0" style=" width:100%;border:0px solid red;line-height:10px">
             <tr style="border:0px">

                 <td colspan="2" style="font-weight: bold">

                     {{changeDateformatTime($note->created_datetime)}}

                 </td>
             </tr>
             <tr>
                 <td>
                     @if($note->contact)

                     <table style="line-height:15px; ">
                         <tr style="border-bottom:1px solid #8f8f8f">
                             <td colspan="2">
                                 {{ $note->contact->first_name   }} {{ $note->contact->last_name   }}
                             </td>
                         </tr>
                         <tr style="border-bottom:1px solid #8f8f8f; vertical-align:middle;margin:auto">
                             <td style="width:15px;  margin :auto;vertical-align:middle">
                                 <img style=" padding-top:5px;margin :auto;vertical-align:middle" src="{{env('BASE_PUBLIC_URL')}}/icons/email.png" width="15">
                             </td>
                             <td style="  margin :auto">
                                 {{ $note->contact->email? $note->contact->email:'---' }}
                             </td>
                         </tr>

                         <tr style="border-bottom:1px solid #8f8f8f">
                             <td style="  padding-top:5px;;margin :auto">
                                 <img style="margin :auto" src="{{env('BASE_PUBLIC_URL')}}/icons/phone.png" width="15">
                             </td>
                             <td style="  margin :auto">
                                 {{ $note->contact->contact_number? $note->contact->contact_number:'---'   }}
                             </td>
                         </tr>

                     </table>
                     @endif
                 </td>

             </tr>


         </table>




         <!-- @if($note->contact)


         <div style="text-align:left;font-size:8px;padding-left:50px;margin-top:10px;">
             <div style="font-weight:bold">
                 {{ $note->contact?->first_name }}
                 {{ $note->contact?->last_name }}
             </div>
             <div>
                 {{ $note->contact->phone1 }}
             </div>
             <div>
                 {{ $note->contact->phone2 }}
             </div>
             <div>
                 {{ $note->contact->email }}
             </div>
             <div>
                 {{ $note->contact->whatsapp }}
             </div>
         </div>
         @endif -->
     </td>
     <td
         style=" border-left: 1px solid #ddd;  vertical-align: middle;   text-align: center;width:70px  ">
         <div
             style=" position: relative;  left: -24px; width: 1px; height: 1px; border-radius: 50%;  padding: 20px; border: 1px solid #000; background-color: #fff;    display: flex; justify-content: center; align-items: center;  ">
             <div style="text-align: center">
                 @if (env('APP_ENV') !== 'local')
                 <img
                     style=" border-radius: 50%; width: 35px; max-width: 35px; margin-top:-16px "
                     src="{{ $note->contact?->profile_picture ? $note->contact->profile_picture : 'https://alarmbackend.xtremeguard.org/unknown-person-icon.png' }}" />

                 @endif

                 <img
                     style=" border-radius: 50%; width: 35px; max-width: 35px; margin-top:-16px "
                     src="{{ getcwd() .'/no-profile-image.jpg' }}" />


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
                 style=" border: 1px solid #ddd; border-radius: 6px; padding-left: 20px; padding-top: 20px;padding-right: 20px; margin-top: -10px; height:auto ">
                 <div style="font-weight: bold; font-size: 14px">
                     {{$note->contact?->address_type??'---'}}
                 </div>
                 <div style="padding-top: 10px; font-size: 10px">
                     <span class="bold">Notes: </span>{{ $note->notes }}
                 </div>

                 <hr style="color:#ddd;margin-top:10px " />
                 <table style=font-size:10px;>
                     <tr>
                         <td style="font-weight:bold;width:60px;">Call Status
                         </td>
                         <td>: {{ $note->call_status }}

                         </td>
                         <td style="font-weight:bold;width:70px;">Call Response

                         </td>
                         <td>: {{ $note->response }}

                         </td>
                         <td style="font-weight:bold;width:70px;">Event Status

                         </td>
                         <td>: {{ $note->event_status }}

                         </td>
                     </tr>
                 </table>

                 <div style="height:20px"> </div>
             </div>


         </div>
     </td>
 </table>
 <!-- Contact -->