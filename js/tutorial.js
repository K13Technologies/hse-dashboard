$(document).ready(function(){
    function getOrientation(component,step){
        switch (component){
            case "divisions":
                return getDivisionOrientation(step);
            case "admins":
                return getAdminOrientation(step);
            case "workers":
                return getWorkersOrientation(step);
            case "signin":
                return getSigninOrientation(step);
            case "categories":
                return getCategoriesOrientation(step);
        }
    }
    function getDivisionOrientation(step){
        switch (step){
            case 1:
                return 'right';
            case 2:
                return 'right';
            case 3:
                return 'top';
            case 4:
                return 'top';
            case 5:
                return 'top';
            case 6:
                return 'top';
            case 7:
                return 'right';
            case 8:
                return 'top';
            default:
                return 'right';
        }
    }
    function getAdminOrientation(step){
        switch (step){
            case 1:
                return 'right';
            case 2:
                return 'right';
            case 3:
                return 'top';
            case 4:
                return 'top';
            case 5:
                return 'top';
            case 6:
                return 'right';
            default:
                return 'right';
        }
    }
    
    function getWorkersOrientation(step){
        switch (step){
            case 1:
                return 'right';
            case 2:
                return 'right';
            case 3:
                return 'top';
            case 4:
                return 'top';
            case 5:
                return 'top';
            case 6:
                return 'top';
            case 7:
                return 'right';
            default:
                return 'right';
        }
    }
    
    
    function getSigninOrientation(step){
        switch (step){
            case 1:
                return 'right';
            case 2:
                return 'right';
            case 3:
                return 'top';
            case 4:
                return 'top';
            case 5:
                return 'top';
            default:
                return 'right';
        }
    }
    
    function getCategoriesOrientation(step){
        switch (step){
            case 1:
                return 'right';
            case 2:
                return 'right';
            case 3:
                return 'right';
            case 4:
                return 'right';
            case 5:
                return 'right';
            case 6:
                return 'right';
            case 7:
                return 'right';
            default:
                return 'right';
        }
    }
    
    function showBackButton(component,step){
        if (component == "divisions" && step == 8) {
            $("#backToTutorial").removeClass('hidden');
            return;
        }
        if (component == "admins" && step == 6) {
            $("#backToTutorial").removeClass('hidden');
            return;
        }
        if (component == "workers" && step == 7) {
            $("#backToTutorial").removeClass('hidden');
            return;
        }
        if (component == "signin" && step == 5) {
            $("#backToTutorial").removeClass('hidden');
            return;
        }
        if (component == "categories" && step == 12) {
            $("#backToTutorial").removeClass('hidden');
            return;
        }
        $("#backToTutorial").addClass('hidden');
    }
    
    
    $("label.radio").click(function(){
       $("#continueTutorialMenu").removeClass('hidden');
    });
    
    var step = 1;
    var component = "";
    $(".tut-button").click(function(){
       $('input[name=tutOption]:checked').removeProp('checked');
       $("#modalTutorialMenu").modal('show');
    });
    
    $("#backToTutorial").click(function(){
        $('input[name=tutOption]:checked').parent().next().find('input').first().prop('checked','checked');
        $("#modalTutorialBox").modal('hide');
        $("#modalTutorialMenu").modal('show');
    });
    
    $("#continueTutorialMenu").click(function(){
        var selectedOpt = $('input[name=tutOption]:checked', '#tutorialMenuForm');
        component = $(selectedOpt).val();
        step = 1;
        var title = $(selectedOpt).prop('title');
        $("#modalTutorialMenu").modal('hide');
        $("#modalTutorialBoxTitle").text(title);
        orientation = getOrientation(component,step);
        updateBox(component,step,orientation);
        $("#modalTutorialBox").modal('show');
        if(displayTutorial === 1){
            $.get(site+'profile/disable-tutorial');
        }
    });
    $("#skipTutorialMenu").click(function(){
         $.get(site+'profile/disable-tutorial');
    });
    
    function updateBox(component,step,orientation){
            var divId = component+step;
            $(".popup-bubble").remove();
            var content = "<div class='floater-"+orientation+" popup-bubble' id='popup-"+divId+"'>"+$("#"+divId).html()+"</div>";
            $("#modalTutorialBox").append(content);
            $("#tut-photo").html($("#img-"+divId));
            showBackButton(component,step);
    }
    
    $("#modalTutorialBox").on('click',".tut-next",function(){
        step +=1;
        orientation = getOrientation(component,step);
        updateBox(component,step,orientation);
    });
    
    $("#modalTutorialBox").on('click',".tut-prev",function(){
        step -=1;
        orientation = getOrientation(component,step);
        updateBox(component,step,orientation);
    });
    
    if(displayProfile === 1){
        $("#profile-menu-button").trigger('click');
    }else if(displayTutorial === 1){
        $(".tut-button").trigger('click');
    }
});

