/**
 * Openbiz browser javascript library
 * @author rockys swen
 */

// MenuService
var openbizServices = angular.module('Openbiz.services', []);
openbizServices.service('MenuService',function ($http, $location) {

	var dataService;
    var appMenuTree;
	var appMainTab;
	var breadcrumb;
	var onRequest = false;
	var menuList;
	
	this.init = function (dataService) {
		this.dataService = dataService;
	}
	
	this.getMenuTree = function (queryString, callback) {
		console.log("enter getMenuTree");
		var url = this.dataService+'/q?format=json';
		if (queryString) url += '&'+queryString;
		$http.get(url).success(function(responseObj) {
			console.log("return getMenuTree responseObj");
			callback(responseObj);
			//return responseObj;
		});
    }
    /*
    this.getAppMenu = function (queryString) {
		this.appMenuTree = this.getMenuTree(queryString);
		this.matchLocationPath();
		return this.appMenuTree;
    }
	
	this.getMainTab = function (queryString) {
		this.appMainTab = this.getMenuTree(queryString);
		return this.appMainTab;
	}
	*/
	this.getBreadcrumb = function () {
		return this.breadcrumb;
	}
	
	this.matchLocationPath = function (menuTree) {
		console.log("enter matchLocationPath");
		// find the current node by matching with location url
		for (var i=0; i<menuTree.length; i++) {
			menuTree[i].m_Current = $location.path() == menuTree[i].m_URL ? 1:0;
			if (menuTree[i].m_ChildNodes) {
				for (var j=0; j<menuTree[i].m_ChildNodes.length; j++) {
					menuTree[i].m_ChildNodes[j].m_Current = $location.path() == menuTree[i].m_ChildNodes[j].m_URL ? 1:0;
					if (menuTree[i].m_ChildNodes[j].m_Current == 1) {
						menuTree[i].m_Current = 1;
						console.log(menuTree[i].m_ChildNodes[j]);
						//break;
					}
				}
			}
			/*if (menuTree[i].m_Current == 1) {
				break;
			}*/
		}
	}
});

/**
 * Define a parent controller.
 *
 * @param {Object} $scope
 */
function TableFormController($scope, $http, $location, $compile) {
	$scope.initQueryString = "";
	$scope.searchQueryString = "";
	$scope.currentPage = 1;
	$scope.totalPage = 1;
	$scope.sort = "";
	$scope.sorder = "";
	$scope.selectedIndex = 0;
	$scope.selectedId = 0;
	$scope.urlPath = $location.path();

	$scope.init = function(name, dataService, queryString) {
		$scope.name = name;
		$scope.dataService = dataService;
		$scope.initQueryString = queryString;
		$scope.searchQueryString = queryString;
		
		$scope.gotoPage(1);
	}

	$scope.gotoPage = function(page) {
		if (page < 1) return;
		if (page > $scope.totalPage) return;
		$scope.selectedIndex = 0;
		$scope.fetchData(page, $scope.sort, $scope.sorder, $scope.searchQueryString);
	}
	
	$scope.sortRecord = function(field) {
		// if sort on the field, toggle the sort order
		if (field == $scope.sort) {
			if ($scope.sorder == "") fieldOrder = "asc";
			else if ($scope.sorder == "asc") fieldOrder = "desc";
			else fieldOrder = "asc";
		}
		else {
			fieldOrder = "asc";
		}
		$scope.fetchData(1, field, fieldOrder, $scope.searchQueryString);
	}
	
	$scope.search = function() {
		// run search with user input on searchPanel
		if (typeof $scope.searchPanel != 'undefined' && $scope.searchPanel != null) {
			var elemValues = [];
			for (var key in $scope.searchPanel) {
				if ($scope.searchPanel[key] != "") {
					elemValues.push(key+"="+$scope.searchPanel[key]);
				}
			}
			var queryString = elemValues.join("&");
			if ($scope.initQueryString == "" && queryString != "") $scope.searchQueryString = queryString;
			else if ($scope.initQueryString != "" && queryString == "") $scope.searchQueryString = $scope.initQueryString;
			else $scope.searchQueryString = "";
			$scope.selectedIndex = 0;
			$scope.fetchData(1, $scope.sort, $scope.sorder, $scope.searchQueryString);
		}
	}
		
	$scope.selectRow = function (index) {
		console.log("selected id "+index);
		if (!$scope.dataset || !$scope.dataset[index]) return;
		// change the style of selected row
		if ($scope.selectedIndex < $scope.dataset.length) {
			$scope.dataset[$scope.selectedIndex].selected = 0;
		}
		if (index >= $scope.dataset.length) {
			index = $scope.dataset.length-1;
		}
		$scope.dataset[index].selected = 1;
		$scope.selectedIndex = index;
		$scope.selectedId = $scope.dataset[index].Id;
	}
	
		
	$scope.delete = function (index) {
		console.log("to delete index "+index);
		var id = $scope.dataset[index].Id
		// ask for user to confirm deletion
		alertMsg = "Are you sure you want to delete the selected record(s)?";
        if (!confirm(alertMsg))
    		return;
		
		// call web service to delete the record
		var url = $scope.dataService+'/'+id+'?format=json';
		$http.delete(url).success(function(responseObj) {
			console.log("successfully deleted record "+id);
			// reload the list
			$scope.gotoPage($scope.currentPage);
		}).error(function(message, status) {
			alert(status + " " + message);
			return;
		});
	}
	
	$scope.refresh = function () {
		$scope.gotoPage($scope.currentPage);
	}
	
	$scope.dialog = function (url, w, h) {
		var _url = APP_INDEX+url;
		$http.get(_url).success(function(response) {
			openDialog($compile(response)($scope),w,h);
		}).error(function(message, status) {
			alert(status + " " + message);
			return;
		});
	}
	
	$scope.closeDialog = function () {
		closeDialog();
	}
	
	$scope.addToParent = function (childId) {
		var url = $scope.dataService;
		$http.post(url,{id:childId}).success(function(response) {
			closeDialog();
			$scope.refresh();
		}).error(function(message, status) {
			alert(status + " " + message);
			return;
		})
	}
	
	$scope.removeFromParent = function (index) {
		console.log("to remove index "+index);
		var childId = $scope.dataset[index].Id
		// ask for user to confirm deletion
		alertMsg = "Are you sure you want to remove the selected record(s)?";
        if (!confirm(alertMsg))
    		return;
		// call web service to delete the record
		var url = $scope.dataService+'/'+childId+'?format=json';
		$http.delete(url).success(function(responseObj) {
			console.log("successfully removed record "+childId);
			// reload the list
			$scope.refresh();
		}).error(function(message, status) {
			alert(status + " " + message);
			return;
		});
	}
	
	$scope.pickRecords = function () {
		// call parent's addToParent() to add selected records to parent
		var id = $scope.dataset[$scope.selectedIndex].Id
		$scope.$parent.addToParent(id);
	}
	
	$scope.fetchData = function (page, sortField, sortOrder, queryString) {
		var url = $scope.dataService+'/q?format=json';
		if (page != null) url += '&page='+page;
		if (sortField && sortOrder) url += '&sort='+sortField+'&sorder='+sortOrder;
		if (queryString) url += '&'+queryString;
		$http.get(url).success(function(responseObj) {
			$scope.dataset = responseObj.data;
			$scope.totalPage = responseObj.totalPage;
			if ($scope.totalPage == 0) $scope.totalPage = 1;
			$scope.currentPage = page;
			$scope.sort = sortField;
			$scope.sorder = sortOrder;
			$scope.selectRow($scope.selectedIndex);
		}).error(function(message, status) {
			alert(status + " " + message);
			return;
		});
	}
}

function TableMenuController($scope, $http, $location, $compile, $injector) {
	$injector.invoke(TableFormController, this, {$scope:$scope});
	
	$scope.parentNodes = null;
	
	$scope.listChildren = function (id) {
		console.log("listChildren of menu id as "+id);
		// change the queryString and rerun the query
		$scope.initQueryString = "PId="+id;
		$scope.searchQueryString = $scope.initQueryString;
		
		$scope.gotoPage(1);
	}
	
	$scope.fetchData = function (page, sortField, sortOrder, queryString) {
		var url = $scope.dataService+'/q?format=json';
		if (page != null) url += '&page='+page;
		if (sortField && sortOrder) url += '&sort='+sortField+'&sorder='+sortOrder;
		if (queryString) url += '&'+queryString;
		$http.get(url).success(function(responseObj) {
			$scope.dataset = responseObj.data;
			$scope.totalPage = responseObj.totalPage;
			if ($scope.totalPage == 0) $scope.totalPage = 1;
			$scope.currentPage = page;
			$scope.sort = sortField;
			$scope.sorder = sortOrder;
			$scope.selectRow($scope.selectedIndex);
			// take parent nodes from response object
			$scope.parentNodes = responseObj.parentNodes;
		}).error(function(message, status) {
			alert(status + " " + message);
			return;
		});
	}
}
TableMenuController.prototype = Object.create(TableFormController.prototype);

function CFormController($scope, $resource, $window, $location) {
	$scope.dataobj = {};
	$scope.id = '';
	
	$scope.init = function(name, dataService, queryString) {
		$scope.name = name;
		$scope.dataService = dataService;
		$scope.id = getQueryVariable(queryString, 'Id');
		
		var Model = $resource(dataService+'/:id'+'?format=json',{id: $scope.id});
		
		if ($scope.id == '' || $scope.id == null) {
			$scope.dataobj = new Model();
		}
		else {
			// get the data with given record id
			$scope.dataobj = Model.get({}, function() {
				console.log($scope.dataobj);
			}, function(errorObj) {
				console.log(errorObj);
			});
		}
	}
	
	$scope.save = function(redirectPage) {
		$scope.clearMessages();
		var formData = angular.copy($scope.dataobj);
		formData.$save(function(data) {
			console.log("Data is successfully saved.");
			if (typeof redirectPage != 'undefined' && redirectPage != null) {
			var redirectUrl = APP_INDEX+redirectPage+data.Id;
				console.log("Redirect to page "+redirectUrl);
				//$window.location.href = redirectUrl;	// this is full page load
				$location.path(redirectUrl);	// this is not full page load
			}
		}, function(errorObj) {
			// errorObj.data, errorObj.status, errorObj.statusText
			console.log(errorObj);
			if (typeof errorObj.data == 'string') $scope.errorMsg = errorObj.data;
			else $scope.errors = errorObj.data;
		});
	}
	
	$scope.delete = function(redirectPage) {
		$scope.clearMessages();
		var id = $scope.dataobj.Id;
		console.log("to delete id "+id);
		// ask for user to confirm deletion
		alertMsg = "Are you sure you want to delete record with id as "+id+"?";
        if (!confirm(alertMsg))
    		return;
		
		var formData = angular.copy($scope.dataobj);
		formData.$delete(function(data) {
			console.log("Data is successfully saved.");
			var redirectUrl = APP_INDEX+redirectPage;
			console.log("Redirect to page "+redirectUrl);
			$location.path(redirectUrl);
		}, function(errorObj) {
			console.log(errorObj);
		});
	}
	
	$scope.clearMessages = function() {
		$scope.errors = null;
		$scope.errorMsg = null;
		$scope.noticeMsg = null;
	}
	
	$scope.submit = function() {
	
	}
	
	$scope.back = function() {
		history.go(-1);
	}
}

function getQueryVariable(queryString, variable) {
    var vars = queryString.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    console.log('Query variable %s not found', variable);
}


function LeftMenuController($scope, $http, $location, MenuService) {
	$scope.init = function(name, dataService, queryString) {
		$scope.name = name;
		$scope.dataService = dataService;

		MenuService.init(dataService);
		$scope.treeNodes = MenuService.getMenuTree(queryString, function(data){
			MenuService.matchLocationPath(data);
			$scope.treeNodes = data;
		});
		
		console.log("location path is "+$location.path());
	}

	$scope.$on('$routeChangeSuccess', function(event) {
		console.log("route changed, "+$location.path());
		if ($scope.treeNodes) {
			MenuService.matchLocationPath($scope.treeNodes);
		}
	});
	
	$scope.showSubmenu = function(menu_id) {
		// set the menu_id.m_Current as 1
		for (i=0; i<$scope.treeNodes.length; i++) {
			if ($scope.treeNodes[i].m_Id == menu_id) {
				$scope.treeNodes[i].m_Current = $scope.treeNodes[i].m_Current == 1 ? 0:1;
				break;
			}
		}
	}
	
	$scope.isCurrent = function(path) {
		return $location.path() == path;
	}
}

function openDialog(content, w, h) {
	$('#modal_dialog').remove();
	var d = document.createElement('DIV');
	document.body.appendChild(d);
	$(d).attr('id', 'modal_dialog');
	options = {width:w, height:h, modal:true};
	$(d).html(content);
	$(d).dialog(options);
}

function closeDialog() {
	$('#modal_dialog').dialog("close");
}