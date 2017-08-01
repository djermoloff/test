questApp.controller('SignUp',
    function ($scope, $http){
		$scope.save = function (user, answerForm){
			if (user.password != user.repeat_password) {
				var $arr = {};
				$arr['status']='error';
				$arr['error'] = $('#inputPasswordConfirm').data('error');
				$scope.result = $arr;
				return false;
			}
			
			if($("#terms").is(':checked') == false) {
				var $arr = {};
				$arr['status']='error';
				$arr['error'] = $('#terms').data('error');
				$scope.result = $arr;
				return false;
			}
			
            if(answerForm.$valid){
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Account/SignUp/";
                $query = "email="+user.email+"&name="+user.name+"&password="+user.password;
				$http.get($url+"?"+$query).
				then(function success(response) {
					$scope.result = response.data;
					if ($scope.result.status == "ok") {
						setTimeout("goLink('cabinet/authorization')", 5000);
					}
				});
            }
        };
	}
)

questApp.controller('Auth',
    function ($scope, $http){
		$scope.authorization = function (user, answerForm){
            if(answerForm.$valid){
                $url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Auth/Authorization/";
				$query = "email="+user.email+"&password="+user.password;
				$http.get($url+"?"+$query).
				then(function success(response) {
					if (response.data.status == "ok") {
						goLink('cabinet/home');
					} else {
						$scope.result = response.data;
					}
				});
            }
        };
	}
)

questApp.controller('FogotPassword',
    function ($scope, $http){
		$scope.restoration = function (user, answerForm){
            if(answerForm.$valid){
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Auth/FogotPassword/";
                $query = "email="+user.email;
				$http.get($url+"?"+$query).
				then(function success(response) {
					$scope.result = response.data;
				});
            }
        };
		
		$scope.changePassword = function (user, answerForm){
			if (user.password != user.repeat_password) {
				var $arr = {};
				$arr['status']='error';
				$arr['error'] = $('#inputPasswordConfirm').data('error');
				$scope.result = $arr;
				return false;
			}
			
            if(answerForm.$valid){
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Auth/ChangePassword/";
                $query = document.location.search.replace("?","") + "&password="+user.password;
				$http.get($url+"?"+$query).
				then(function success(response) {
					$scope.result = response.data;
					if ($scope.result.status == "ok") {
						setTimeout("goLink('cabinet/authorization')", 5000);
					}
				});
            }
        };
	}
)

function goLink ($u) {
	$link = document.location.protocol + "//" + document.location.host + "/" + $u;
	document.location.replace($link);
}