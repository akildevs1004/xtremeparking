<table style="width:100%; text-align:center; border-collapse:collapse;">

    <!-- BLUE SEPARATOR LINE -->
    <tr>
        <td colspan="3" style="background-color:#156082; height:5px; padding:0; margin:0;"></td>
    </tr>

    <tr>
        <!-- LEFT SECTION: COMPANY NAME + PHONE -->
        <td style="width:33%; vertical-align:middle; text-align:left; padding-top:10px; padding-bottom:5px;">
            <div style="font-weight:bold; font-size:12px;">
                {{ $company->name }}
            </div>

            <div style="font-size:11px; margin-top:3px;">
                Phone: {{ $company->contact->number ?? '---' }}
            </div>
        </td>

        <!-- CENTER SECTION: EMAIL & WEBSITE (NO BORDERS) -->
        <td style="width:33%; vertical-align:middle; padding-top:10px;">
            <table style="margin:auto; border-collapse:collapse; border:none;">
                <tr style="border:none;">
                    <td style="border:none;">
                        <img src="{{ getcwd() . '\icons\email.png' }}" width="14" style="vertical-align:middle;">

                    </td>
                    <td style="font-size:11px; padding-left:5px; border:none;">
                        {{ $company->user->email ?? '---' }}
                    </td>
                </tr>
                <tr style="border:none;">
                    <td style="border:none;">
                        <img src="{{ getcwd() . '\icons\website.png' }}" width="14" style="vertical-align:middle;">
                    </td>
                    <td style="font-size:11px; padding-left:5px; border:none;">
                        ---
                    </td>
                </tr>
            </table>
        </td>

        <!-- RIGHT SECTION: PO BOX + LOCATION -->
        <td style="width:33%; vertical-align:middle; text-align:left; padding-left:50px; padding-top:10px;">
            <div style="font-size:11px;">
                P.O. Box:
                {{ $company->p_o_box_no == 'null' ? '---' : $company->p_o_box_no }}
            </div>

            <div style="font-size:11px; margin-top:3px;">
                {{ $company->location }}
            </div>
        </td>
    </tr>

</table>
