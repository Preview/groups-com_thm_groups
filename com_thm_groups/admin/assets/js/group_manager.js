function deleteModerator(groupId, userId){
    document.getElementsByName('task')[0].value="group.deleteModerator";
    document.getElementsByName('g_id')[0].value=groupId;
    document.getElementsByName('u_id')[0].value=userId;
    document.adminForm.submit();
}

function deleteRole(groupId, roleId){
    document.getElementsByName('task')[0].value="group.deleteRole";
    document.getElementsByName('g_id')[0].value=groupId;
    document.getElementsByName('r_id')[0].value=roleId;
    document.adminForm.submit();
}

function deleteProfile(groupId, profileId){
    document.getElementsByName('task')[0].value="group.deleteProfile";
    document.getElementsByName('g_id')[0].value=groupId;
    document.getElementsByName('p_id')[0].value=profileId;
    document.adminForm.submit();
}

function confirmMsg(){
    var msg = "You leave the component THM Groups!";
    if(!confirm(msg)){
        return false;
    }
}

var jq = jQuery.noConflict();
jq(document).ready(function() {
   jq('#toolbar-new').on('click', function(){
      confirmMsg();
   });
});




