$(function(){
	var btnUpload=$('#upload');
	new AjaxUpload(btnUpload, {
		action: '/api/Account/AddAvatar/',
		name: 'file',
		onSubmit: function(file, ext){
			if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){ 
				alert("Only JPG, PNG, GIF");
				return false;
			}
		},
		onComplete: function(file, response){
			response = JSON.parse(response);
			if (response.status == "ok") {
				$('#img_avatar').attr('src', response.url_avatar);
				this.removeAttr('disabled');
			}else {
				$scope.alert_message.error = response.error;
			}
		}
	}); 
});