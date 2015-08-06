angular.module('filters',["ui.bootstrap"]).filter('sum', function() {
    return function(input) {
        result = 0;
        if (typeof input == 'undefined') return 0;
        for (i=0;i<input.length;i++) {
            result += input[i].amount;
        }
        return result;
    };
});

var app = angular.module('BaseModule', ['ngResource','filters']).config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});


app.factory('Tag', ['$resource', function($resource) {
    return $resource('/tags.json/:id', null,
        {
            'save':   { method:'POST', headers : {'Content-Type': 'application/x-www-form-urlencoded'}},
            'query':  { method:'GET', isArray: true }
        });
}]);

app.factory('Source', ['$resource', function($resource) {
    return $resource('/sources.json/:id', null,
        {
            'save':   { method:'POST', headers : {'Content-Type': 'application/x-www-form-urlencoded'}},
            'query':  { method:'GET', isArray: true }
        });
}]);

app.factory('AjusteSaldo', ['$resource', function($resource) {
    return $resource('/ajuste_saldo.json/:id', null,
        {
            'save':   { method:'POST', headers : {'Content-Type': 'application/x-www-form-urlencoded'}},
            'query':  { method:'GET', isArray: true }
        });
}]);

app.factory('Movement', ['$resource', function($resource) {
    return $resource('/movement.json/:id', null,
        {
            'save':   { method:'POST', headers : {'Content-Type': 'application/x-www-form-urlencoded'}},
            'query':  { method:'GET', isArray: true }
        });
}]);

app.config(['$httpProvider', function($httpProvider) {
    $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
}]);

app.controller("TagController", ['$scope', '$log', 'Tag', function($scope, $log, Tag) {
    $scope.tags = Tag.query();
    $scope.newTag = function(new_tag) {
    var tag = new Tag.save( $.param({ 'name' : new_tag}),
        function(data) {
            if (data.status)
                toastr['info'](data.message, "Etiquetas");
            else
                toastr['warning'](data.message, "Etiquetas");
            $scope.tags = Tag.query();
        },
        function(data) {
            toastr['info'](data.message, "Etiquetas")
            $scope.sources = Source.query();
        });
    }
}]);

app.controller("SourceController", ['$scope', 'Movement', 'AjusteSaldo', 'Source', '$log', function($scope, Movement, AjusteSaldo, Source, $log) {
    $scope.sources = Source.query();

    $scope.initGlobal = function(id) {
        $scope.source = Source.get({ 'id': id });
    }

    $("#date_movement").datepicker();

    $scope.newSource = function(new_source) {
    var source = new Source.save( $.param({ 'name' : new_source}),
        function(data) {
            if (data.status)
                toastr['info'](data.message, "Origenes");
            else
                toastr['warning'](data.message, "Origenes");
            $scope.sources = Source.query();
        },
        function(data) {
            toastr['info'](data.message, "Origenes")
            $scope.sources = Source.query();
        });
    }

    $scope.ajusteSaldo = function() {
        var ajuste = new AjusteSaldo.save($.param({'new_amount': $scope.amount, 'source': $scope.source.slug}),
            function(data) {
                toastr['info'](data.message, "Saldo");
                 $scope.amount = null;
                 $scope.source = Source.get({'id': $scope.source.id});
            },
            function(data) {
                toastr['warning'](data.message, "Saldo");
            });
    }

    $scope.newMovement = function() {
        var amount = $scope.movement_amount;
        if ($('#radio54').prop('checked')) {
            var amount = -amount;
        }
        var ajuste = new Movement.save($.param({'amount': amount, 'source': $scope.source.slug, 'tags': $scope.selected_tags }),
            function(data) {
                $scope.selected_tags = [];
                $scope.movement_amount = null;
                toastr['info'](data.message, "Saldo");
                $scope.source = Source.get({'id': $scope.source.id});
            },
            function(data) {
                toastr['warning'](data.message, "Saldo");
            });
    }
}]);

app.controller("BaseController", function($scope) {
    $scope.select = function(option) {
        $scope.selectedOption = option;
    }
    $scope.isSelected = function(option) {
        return $scope.selectedOption == option;
    }
});