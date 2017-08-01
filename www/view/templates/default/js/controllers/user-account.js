questApp.controller('UserAccount',
    function ($scope, $http, $sce){
		$scope.page = document.location.pathname.substring(1);
		$url = document.location.protocol + "//" + document.location.host;
		$url += "/api/Account/main";
		$query = "";
		$http.get($url+$query).
		then(function success(response) {
			if (response.status == 200) {
				$scope.url = response.data.url;
				$scope.user = response.data.user;
				$scope.lg = response.data.lg;
			}
		});
		
		$scope.deposit = function (deposit, depositForm){
			if(depositForm.$valid){
				$scope.page = document.location.pathname.substring(1);
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Payment/Deposit/?";
				$query = "type="+deposit.type+"&amount="+deposit.amount;
				$http.get($url+$query).
				then(function success(response) {
					if (response.status == 200) {
						if (response.data.status == "ok") {
							$scope.deposit.form = $sce.trustAsHtml(response.data.form);
							setTimeout(SendDeposit, 0);
						}
					}
				});
			}
		}
		
		$scope.withdrawal = function (withdrawal, withdrawalForm){
			if(withdrawalForm.$valid){
				$scope.page = document.location.pathname.substring(1);
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Payment/Withdrawal/?";
				$query = "type="+withdrawal.type+"&amount="+withdrawal.amount;
				if (withdrawal.pin != undefined) $query += "&pin="+withdrawal.pin;
				$http.get($url+$query).
				then(function success(response) {
					if (response.status == 200) {
						if (response.data.status == "ok") {
							$scope.user.partner_balance = response.data.partner_balance;
							$scope.alert_message = response.data;
							$('#Withdrawal').modal('hide');
							$scope.withdrawal.amount = "";
							$scope.withdrawal.getPin = "";
							$scope.alert_withdrawal = "";
						}
						if (response.data.status == "smsSent") {
							$scope.withdrawal.getPin = "getPin";
							$scope.alert_withdrawal = response.data;
						}
						if (response.data.status == "error") {
							$scope.alert_withdrawal = response.data;
							if (response.data.attemps <= 0) {
								$scope.alert_message = response.data;
								$('#Withdrawal').modal('hide');
								$scope.withdrawal.amount = "";
								$scope.withdrawal.getPin = "";
								$scope.withdrawal.pin = "";
								$scope.alert_withdrawal = "";
							}
						}
					}
				});
			}
		}
		
		$scope.include = function (include, includeForm){
			if(includeForm.$valid){
				$scope.page = document.location.pathname.substring(1);
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Payment/PartnerToBalance/?";
				$query = "amount="+include.amount;
				if (include.pin != undefined) $query += "&pin="+include.pin;
				$http.get($url+$query).
				then(function success(response) {
					if (response.status == 200) {
						if (response.data.status == "ok") {
							$scope.user.partner_balance = response.data.partner_balance;
							$scope.alert_message = response.data;
							$('#Include').modal('hide');
							$scope.include.amount = "";
							$scope.include.getPin = "";
							$scope.alert_include = "";
						}
						if (response.data.status == "smsSent") {
							$scope.include.getPin = "getPin";
							$scope.alert_include = response.data;
						}
						if (response.data.status == "error") { 
							$scope.alert_include = response.data;
							if (response.data.attemps <= 0) {
								$scope.alert_message = response.data;
								$('#Include').modal('hide');
								$scope.include.amount = "";
								$scope.include.getPin = "";
								$scope.include.pin = "";
								$scope.alert_include = "";
							}
						}
					}
				});
			}
		}
		
		$scope.transfer = function (transfer, transferForm){
			if(transferForm.$valid){
				$scope.page = document.location.pathname.substring(1);
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Payment/Transfer/?";
				$query = "amount="+transfer.amount+"&email="+transfer.email;
				if (transfer.pin != undefined) $query += "&pin="+transfer.pin;
				$http.get($url+$query).
				then(function success(response) {
					if (response.status == 200) {
						if (response.data.status == "ok") {
							$scope.user.balance = response.data.balance;
							$scope.alert_message = response.data;
							$('#Transfer').modal('hide');
							$scope.transfer.amount = "";
							$scope.transfer.getPin = "";
							$scope.alert_transfer = "";
						}
						if (response.data.status == "smsSent") {
							$scope.transfer.getPin = "getPin";
							$scope.alert_transfer = response.data;
						}
						if (response.data.status == "error") { 
							$scope.alert_transfer = response.data;
							if (response.data.attemps <= 0) {
								$scope.alert_message = response.data;
								$('#Transfer').modal('hide');
								$scope.transfer.amount = "";
								$scope.transfer.getPin = "";
								$scope.transfer.pin = "";
								$scope.alert_transfer = "";
							}
						}
					}
				});
			}
		}
	}
)

questApp.controller('UserPayments',
    function ($scope, $http){
		$scope.page = document.location.pathname.substring(1);
		$url = document.location.protocol + "//" + document.location.host;
		$url += "/api/Account/payments";
		$query = "";
		$http.get($url+$query).
		then(function success(response) {
			if (response.status == 200) {
				if (response.data.status == "ok") $scope.payments = response.data.payments;
			}
		});
	}
)

function SendDeposit(){alert(0);
	document.getElementById('SendDeposit').submit();
	}

	questApp.controller('UserPartners',
    function ($scope, $http){
		$scope.page = document.location.pathname.substring(1);
		$url = document.location.protocol + "//" + document.location.host;
		$url += "/api/Account/partners";
		$query = "";
		$http.get($url+$query).
		then(function success(response) {
			if (response.status == 200) {
				if (response.data.status == "ok") $scope.partners = response.data.partners;
			}
		});
		
		var clipboard = new Clipboard('#copy', {
        target: function() {alert($scope.lg.coped);
            return document.querySelector('#foo');
        }
		});

		clipboard.on('success', function(e) {
			console.log(e);
		});

		clipboard.on('error', function(e) {
			console.log(e);
		});
	}
)

questApp.controller('UserProfile',
    function ($scope, $http){
		$scope.page = document.location.pathname.substring(1);
		$url = document.location.protocol + "//" + document.location.host;
		$url += "/api/Account/profile";
		$query = "";
		$http.get($url+$query).
		then(function success(response) {
			if (response.status == 200) {
				if (response.data.status == "ok") {
					$scope.profile = response.data.profile;
					$scope.country_list = response.data.country_list;
				}
			}
		});
		
		$scope.change = function (profile, profileForm){
            if(profileForm.$valid){
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Account/ChangeProfile/?";
				$query = "";
				for (var key in profile) $query += key+"="+profile[key]+"&";
				$http.get($url+$query).
				then(function success(response) {
					$scope.alert_message = response.data;
				});
            }
        };
		
		$scope.changeContact = function (profile, profileForm){
            if(profileForm.$valid){
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Account/ChangeContact/?";
				$query = "";
				for (var key in profile) {
					if (profile[key] != undefined) $query += key+"="+profile[key]+"&";
				}
				$http.get($url+$query).
				then(function success(response) {
					if (response.data.status == "smsSent") 
						$('#GetPin').modal('show');
					else
						$scope.alert_message = response.data;
				});
            }
        };

		$scope.changeDetails = function (profile, profileForm){
            if(profileForm.$valid){
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Account/ChangeDetails/?";
				$query = "";
				for (var key in profile) $query += key+"="+profile[key]+"&";
				$http.get($url+$query).
				then(function success(response) {
					$scope.alert_message = response.data;
				});
            }
        };
		
		$scope.changePassword = function (repass, changePassForm){
			if (repass.pass != repass.repeatpass) {
				var $arr = {};
				$arr['status']='error';
				$arr['error'] = $('#inputPereatPassword').data('error');
				$scope.r_repass = $arr;
				return false;
			}
			
            if(changePassForm.$valid){
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Account/ChangePassword/?";
                $query = "oldpass="+repass.oldpass+"&pass="+repass.pass;
				$http.get($url+$query).
				then(function success(response) {
					$scope.r_repass = response.data;
				});
            }
        };
		
		$scope.confirmPhone = function (getpin, pinForm){
            if(pinForm.$valid){
				$url = document.location.protocol + "//" + document.location.host;
				$url += "/api/Account/ConfirmPhone/?";
				$query = "pin="+getpin.pin;
				$http.get($url+$query).
				then(function success(response) {
					if (response.data.status == "ok") {
						$('#GetPin').modal('hide');
						$scope.alert_message = response.data;
					} else {
						$scope.alert_pin = response.data;
					}
				});
            }
        };
	}
)