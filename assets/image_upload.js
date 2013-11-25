function uploadComplete(event, status, fileName, response) {
    var container = $(event.currentTarget);
    var formContainer = container.find('.uploaded-photos');
    if(formContainer.length == 0) {
        formContainer = $('<ul class="uploaded-photos thumbnails" />').prependTo(container);
    }
    $(response.content).appendTo(formContainer);
}