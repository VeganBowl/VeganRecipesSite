(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['mustache'], factory);
    } else if (typeof exports === 'object') {
        // Node. Does not work with strict CommonJS, but
        // only CommonJS-like environments that support module.exports,
        // like Node.
        module.exports = factory(require('mustache'));
    } else {
        // Browser globals (root is window)
        root.returnExports = factory(root.mustache);
    }
}(this, function (mustache) {
    "use strict";

    /**
     * Fills in default values.
     */
    function merge(obj) {
        for (var i = 1; i < arguments.length; i++) {
            var def = arguments[i];
            for (var n in def) {
                if (obj[n] === undefined) {
                    obj[n] = def[n];
                }
            }
        }
        return obj;
    }

    function hasValue(needle, haystack) {
        for (var i = 0; i < haystack.length; i++) {
            if (haystack[i] === needle) {
                return true;
            }
        }

        return false;
    }

    function camelCase(input) {
        return input.toLowerCase().replace(/-(.)/g, function(match, group1) {
            return group1.toUpperCase();
        });
    }

    function isJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    function getJsonOutOfString(str) {
        if (isJson(str)) {
            return JSON.parse(str);
        } else {
            return [str];
        }
    }

    function cleanArray(array) {
        var i, j, len = array.length, out = [], obj = {};
        for (i = 0; i < len; i++) {
            obj[array[i]] = 0;
        }
        for (j in obj) {
            out.push(j);
        }
        return out;
    }

    function hasClass(ele,cls) {
        return !!ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
    }

    function addClass(ele,cls) {
        if (!hasClass(ele,cls)) ele.className += " "+cls;
    }

    function removeClass(ele,cls) {
        if (hasClass(ele,cls)) {
            var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
            ele.className=ele.className.replace(reg,' ');
        }
    }

    // Built-in defaults
    var defaults = {
        filtersContainerClass: 'filterContainer',       // Container class name
        itemContainerClass: 'filterableContainer',      // Items container class name
        itemClass: 'filterable',                        // Items class name
        filters: [
            {
                type: 'select',
                dataName: 'tag'
            }
        ]
    };

    /** The constructor */
    function Filter(o) {
        this.opts = merge(o || {}, Filter.defaults, defaults);
        this._init();
    }

    // Global defaults that override the built-ins:
    Filter.defaults = {};

    merge(Filter.prototype, {

        _itemsContainer: void 0,

        _filterContainer: void 0,

        _items: void 0,

        _multiValueSelectors: void 0,

        _filteredKeys: {},

        _init: function() {
            var filterContainers = document.getElementsByClassName(this.opts.filtersContainerClass);
            this._filterContainer = filterContainers[0];

            var itemsContainers = document.getElementsByClassName(this.opts.itemContainerClass);
            this._itemsContainer = itemsContainers[0];

            this._items = this._itemsContainer.getElementsByClassName(this.opts.itemClass);

            this._constructFiltersSelectors();
        },

        _constructFiltersSelectors: function() {
            var filters = this.opts.filters;

            for (var i = 0; i < filters.length; i++) {
                var filter = filters[i];

                this._validateFilterConfig(filter);

                this._constructSelector(filter.type, filter.multiple, filter.expanded, filter.dataName);
            }
        },

        _validateFilterConfig: function(filter) {
            if (filter.type == undefined) {
                throw new Error('Missing filter type.');
            }

            if (filter.dataName == undefined) {
                throw new Error('Missing data attribute name.');
            }
        },

        _constructSelector: function(type, multiple, expanded, dataName) {
            switch (type) {
                case 'select':
                    this._constructSelectSelector(false, dataName);
                    break;
                case 'multi-select':
                    this._constructSelectSelector(true, dataName);
                    break;
                case 'radio':
                    this._constructRadioSelector(dataName);
                    break;
                default:
                    throw new Error('Unknown filter type "' + type + '".');
                    break;
            }
        },

        _constructRadioSelector: function(dataName) {
            var items = this._items,
                tags = []
                ;

            for (var i = 0; i < items.length; i++) {
                if (items[i].getAttribute('data-filter-' + dataName) !== undefined) {
                    this._addTagsToList(
                        getJsonOutOfString(items[i].getAttribute('data-filter-' + dataName)),
                        tags
                    );
                }
            }

            var options = [];

            for (var j = 0; j < tags.length; j++) {
                options.push({
                    value: tags[j],
                    label: tags[j]
                });
            }

            var filterHtml = mustache.render(this._getRadioRawHtml(), {
                filter_name: dataName,
                options: options
            });

            var wrapper = document.createElement('div');
            addClass(wrapper, 'radio-style');

            wrapper.innerHTML = filterHtml;

            this._filterContainer.appendChild(wrapper);

            var element = wrapper.firstElementChild;

            var _this = this;
            element.addEventListener('change', function() {
                var value = element.options[element.selectedIndex].value;

                if (hasValue(value, ['-', ''])) {
                    _this._setFilteredKeys(dataName, null);
                } else {
                    _this._setFilteredKeys(dataName, [value]);
                }
            });
        },

        _constructSelectSelector: function(multiple, dataName) {
            var items = this._items,
                tags = []
            ;

            for (var i = 0; i < items.length; i++) {
                if (items[i].getAttribute('data-filter-' + dataName) !== undefined) {
                    this._addTagsToList(
                        getJsonOutOfString(items[i].getAttribute('data-filter-' + dataName)),
                        tags
                    );
                }
            }

            var options = [];

            for (var j = 0; j < tags.length; j++) {
                options.push({
                    value: tags[j],
                    label: tags[j]
                });
            }

            var filterHtml = mustache.render(this._getSelectRawHtml(), {
                filter_name: dataName,
                options: options,
                multiple: multiple
            });

            var wrapper = document.createElement('div');
            addClass(wrapper, 'select-style');

            wrapper.innerHTML = filterHtml;

            this._filterContainer.appendChild(wrapper);

            var element = wrapper.firstElementChild;

            var _this = this;
            element.addEventListener('change', function() {
                var value = element.options[element.selectedIndex].value;

                if (hasValue(value, ['-', ''])) {
                    _this._setFilteredKeys(dataName, null);
                } else {
                    _this._setFilteredKeys(dataName, [value]);
                }
            });


        },

        _addTagsToList: function(tags, list) {
            for (var i = 0; i < tags.length; i++) {
                list.push(tags[i]);
            }
        },

        _setFilteredKeys: function(dataName, values) {
            this._filteredKeys[dataName] = values;

            this._filter();
        },

        _filter: function() {
            for (var i = 0; i < this._items.length; i++) {
                var isRequested = this._isRequested(this._items[i]);

                if (isRequested) {
                    this._show(this._items[i]);
                } else {
                    this._hide(this._items[i]);
                }
            }
        },

        _isRequested: function(el) {
            for (var name in this._filteredKeys) {
                if (this._filteredKeys.hasOwnProperty(name)) {
                    if (null == this._filteredKeys[name]) {
                        return true;
                    }

                    var keys = this._filteredKeys[name];
                    var itemTags = getJsonOutOfString(el.getAttribute('data-filter-' + name));

                    for (var i = 0; i < keys.length; i++) {
                        var tag = keys[i];

                        if (!hasValue(tag, itemTags)) {
                            return false;
                        }
                    }

                    return true;
                } else {
                    throw 'Bad filter name.';
                }
            }
        },

        _hide: function(el) {
            addClass(el, 'filter-hidden');
            removeClass(el, 'filter-visible');
        },

        _show: function(el) {
            addClass(el, 'filter-visible');
            removeClass(el, 'filter-hidden');
        },

        _getSelectRawHtml: function() {
            return '<select {{# multiple }}multiple {{/ multiple }}data-filter-name="{{filter_name}}">' +
                        '<option value="-">-</option>' +
                        '{{#options}}<option value="{{value}}">{{label}}</option>{{/options}}' +
                    '</select>';
        },

        _getRadioRawHtml: function() {
            return '{{#options}}<label><input type="radio" name="{{filter_name}}" value="{{ value }}" data-filter-name="{{filter_name}}" />{{label}}</label>{{/options}}';
        }
    });

    return Filter;
}));
