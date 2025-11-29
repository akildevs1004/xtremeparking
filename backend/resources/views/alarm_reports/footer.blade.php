<table style="width:100%;text-align:center;border:0px solid black">

    <tr>
        <td colspan="100%" style="background-color:#156082;height:5px"></td>
    </tr>
    <tr>

        <td style="width:33%;vertical-align: middle;height:40px;;text-align:left">


            <div style="font-weight:bold">{{ $company->name }}</div>

            Phone1: {{ $company->contact->number ?? '---' }}
        </td>
        <td style="width:33%;vertical-align: middle;height:50px;padding-top:10px;border:0px solid red;margin:auto">
            <table>
                <tr>
                    <td>
                        <img style="margin :auto" src="{{ env('BASE_PUBLIC_URL') }}/icons/email.png" width="15">
                    </td>
                    <td>
                        Email: {{ $company->user->email ?? '---' }}
                    </td>

                </tr>
                <tr>
                    <td>
                        <img style="margin :auto" src="{{ env('BASE_PUBLIC_URL') }}/icons/website.png" width="15">
                    </td>
                    <td>
                        Website:---
                    </td>
                </tr>
            </table>





        </td>
        <td style="width:33%;vertical-align: middle;height:40px;text-align:left;padding-left:50px;"><span
                style=" margin-left: 3px">P.O.Box
                {{ $company->p_o_box_no == 'null' ? '---' : $company->p_o_box_no }}</span>

            <br />
            <span style="margin-left: 3px">{{ $company->location }}</span>

        </td>
    </tr>
</table>
