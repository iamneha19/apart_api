

<!--<div>
     <table border="0" cellpadding="0" cellspacing="0">
                                        <tbody><tr>
                                            <td style="line-height:1.5!important">
                                            <p>
                                                </p><div style="font-family:verdana,sans-serif;font-size:12px"><table align="center" cellpadding="2" cellspacing="2" width="90%">
                    <tbody><tr>
                        <td align="center"><h3><div style="font-family:Arial;font-size:large">{{$meeting['title']}}</div></h3></td>
                    </tr>
                    <tr>
                        <td align="center">

                            <table style="font-family:Arial;font-size:smaller" cellpadding="2" cellspacing="2" width="90%">
                                <tbody><tr>
                                     <td align="right">
                                        <strong>{{$meeting['meeting_on']}}</strong>
                                    </td>
                                </tr>
                                           </tbody></table>
                        </td>
                    </tr>
                    <tr>
                        <td align="left">
                            <table style="font-family:Arial" cellpadding="2" cellspacing="2" width="90%">
                                 <tbody><tr>
                                    <td align="left">
                                        <strong>Created by :</strong>
                                   {{$meeting['created_by']}}                 </td>
    </tr>
    <tr>
        <td align="left">
            <br><strong>Venue :</strong>{{$meeting['venue']}}</td>
    </tr>
    <tr>
        <td align="left">
            <br><strong>Notes :</strong><br>{{$meeting['notes']}}</td>
    </tr>
    <tr>
        <td align="left">
            <br><strong>Meeting Agenda</strong>
        </td>
    </tr>
    <tr>
       <td align="left">
           {{$meeting['agenda']}}
                                    </td>
                                </tr>
                          </tbody></table></td>
                    </tr>
                    <tr>
                        <td align="left">
                        <hr>
                            <strong>Invitees </strong>
                            @if ($meeting['invitees'] == 'O')
                            Only Owners
                            @endif
                            
                             @if ($meeting['invitees'] == 'M')
                            Only Commitee Members
                            @endif
                            
                             @if ($meeting['invitees'] == 'A')
                            All Members
                            @endif
                            </td>
                        </tr></tbody></table></div><p></p></td>
                        <td>&nbsp;</td>
                        <td></td>
                    </tr></tbody></table>                      
</div>
-->

<div>
    <table width="90%" align="center" cellpadding="2" cellspacing="2">
        <tbody>
            <tr>
                <td align="center">
                    <table width="90%" cellpadding="2" cellspacing="2" style="font-family:Arial;font-size:smaller">
                        <tbody>
                            <tr>
                                <td align="center"><h3><div style="font-family:Arial;font-size:large">{{$meeting['title']}}</div></h3></td>
                            </tr>
                            <tr>
                                <td align="right">
                                     <strong>{{$meeting['meeting_on']}}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="left">
                    <table width="90%" cellpadding="2" cellspacing="2" style="font-family:Arial">
                        <tbody>
                            <tr>
                                <td align="left">
                                    <strong>Created by :</strong>
                                     {{$meeting['created_by']}}     
                                </td>
                            </tr>
                            <tr>
                                <td align="left">
                                    <br><strong>Venue :</strong>
                                    {{$meeting['venue']}}
                                </td>
                            </tr>
                            <tr>
                                <td align="left">
                                    <br><strong>Notes :</strong>
                                    {!! $meeting['notes'] !!}
                                </td>
                            </tr>
                            <tr>
                                <td align="left">
                                    <br><strong>Meeting Agenda :</strong>
                                    {{$meeting['agenda']}}
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="left">
                    <hr>
                    <strong>Invitees :</strong>
                    @if ($meeting['invitees'] == 'O')
                        Only Owners
                    @endif

                    @if ($meeting['invitees'] == 'M')
                        {{$role_name}}
                    @endif

                    @if ($meeting['invitees'] == 'A')
                        All Members
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>