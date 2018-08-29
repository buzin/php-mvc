function loadPreview(evt)
{
    var files = evt.target.files; // FileList object
    var f = files[0];
    // Process only image files.
    if (f.type.match("image.*")) {
        var reader = new FileReader();
        // Closure to capture the file information.
        reader.onload = (function(file) {
            return function(e) {
                // Render thumbnail.
                $("#preview-image").html(['<img src="', e.target.result, '"/>'].join(""));
            };
        })(f);
        // Read in the image file as a data URL.
        reader.readAsDataURL(f);
    }
}

function preview()
{
    // Update preview fields.
    $("#preview-name").text($("#user-id option:selected").text());
    $("#preview-email").text($("#user-id option:selected").data("email"));
    $("#preview-text").text($("#text").val());
    $("#preview-status").text($("#status").prop("checked") ? "Completed" : "In progress");

    // Hide form and show preview.
    $("#input").hide();
    $("#preview-btn").hide();
    $("#preview").removeClass("d-none");
}

$(function() {
    $("#image").change(loadPreview);
    $("#preview-btn").click(preview);
});