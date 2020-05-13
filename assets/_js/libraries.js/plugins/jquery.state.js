;(function($) {
	var _self = $.state = $.state || {};


	_self.set = function(key, value) {
		var _parse = lil.uri(window.location.toString());
		var q = _parse.query() || {};

		if ( Object.keys(q).length === 0 ) {
			q = {};
		}


		if (typeof key === 'object'){
			q = Object.assign({}, q,key);
		} else {
			if ( key ) {
				q[key] = value;
			}
		}


		_self._update(q);
		return q;

	};
	_self.get = function(key) {
		var _parse = lil.uri(window.location.toString());
		var q = _parse.query() || {};
		if ( key ) {
			return q[key];
		} else {
			return q;
		}

	};
	_self.remove = function(key) {
		var _parse = lil.uri(window.location.toString());
		var q = _parse.query() || {};

		if ( Array.isArray(key) ) {
			for ( var i in key ) {
				delete q[key[i]];
			}
		} else {
			delete q[key];
		}


		_self._update(q);
	};

	_self._update = function(q) {
		q = JSON.parse(JSON.stringify(q));


		var querystring = "";
		if ( Object.keys(q).length !== 0 ) {
			querystring = $.param(q, true);
		}

		querystring = "?" + decodeURIComponent( querystring );


		history.pushState(null, null, querystring);
	};
})(jQuery);

