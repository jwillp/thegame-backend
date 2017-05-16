// LiteJS : Lite Javascript modules


/**
  * Overwites obj1's properties with obj2's.
  * and add obj2's if not present in obj1.
  */
function mergeObjects(obj1, obj2) {
  var obj3 = {};
  for( var attrname in obj1){
  obj3[attrname] = obj1[attrname];
  }
  for( var attrname in obj2){
  obj3[attrname] = obj2[attrname];
  }
  return obj3;
};


var Lite = {}

/**
 * Used to easily sort objects by a certain field
 * source: http://stackoverflow.com/questions/979256/sorting-an-array-of-javascript-objects
 * usage:
 *
 * Sort by price high to low
 * homes.sort(sort_by('price', true, parseInt));
 *
 * Sort by city, case-insensitive, A-Z
 * homes.sort(sort_by('city', false, function(a){return a.toUpperCase()}));
 */
Lite.sortBy = function(field, reverse, primer){

   var key = primer ?
       function(x) {return primer(x[field])} :
       function(x) {return x[field]};

   reverse = !reverse ? 1 : -1;

   return function (a, b) {
       return a = key(a), b = key(b), reverse * ((a > b) - (b > a));
     }
};



 /**
   * Object firing events
   */
Lite.EventEmitter = function(){
  return {

    // Objects listening to events triggered by this manager
    listeners : [],

   /**
     * Sends events to listeners
     */
    fireEvent : function(eventName, params) {
       for(var i = 0; i < this.listeners.length; i++) {
          var func = this.listeners[i].onEvent['on'+eventName];
          if (typeof func === 'function') {
             func.call(this.listeners[i].onEvent, this, params);
          };

       };
    },

    /**
     * Adds a Listener to the list of listeners
     */
    addListener : function(listener) {
      this.listeners.push(listener);
    },

    /**
     * Adds multiple listeners to the lists of listeners
     */
    addListeners : function(listeners) {
       this.addListener(listeners);
    },
  }
};

/**
 * Listens to event fired by an emitter
 */
Lite.EventListener = function() {
  return {

    /**
     * Registers the current listener to an emitter
     * Automatically listens to Lite.EventManger
     */
    registerToSource : function(emitter) {
      emitter.addListener(this);
      if(emitter !== Lite.EventManager)
        Lite.EventManager.addListener(this);
      this.onEvent.self = this; // Keep a reference to this in the onEventObject
    },

    /**
     * Object containing methods like
     * onClickEvent(emitter)
     * onXYZEvent(emitter);
     * These methods are called by the emitters
     */
    onEvent: {},
  }
};

// Global Event Emitter (every listener will listen to this)
Lite.EventManager = Lite.EventEmitter();

// Class receiving and emitting events
Lite.EventController = function(customClass) {
    var controller =  mergeObjects(Lite.EventEmitter(),
                                   Lite.EventListener());
    return mergeObjects(controller, customClass);
};



/**
 * Function creating Lite Components.
 * Lite Components have several added features
 * to React Components:
 * - Event Listening and emitting capabilities
 */
Lite.createClass = function(customClass) {

  // Event Listening Specs
  var _evListenerComponent = {
      /**
       * This function should not be overriden
       * by a Component, instead use onComponentMounted
       */
      componentDidMount : function() {
        if (typeof this.props.onEvent != 'undefined') {
          this.onEvent = mergeObjects(this.onEvent, this.props.onEvent);
        };
        if (typeof this.props.eventSource != 'undefined'){
          this.registerToSource(this.props.eventSource);
        }
        this.registerToSource(Lite.EventManager);
        this.onComponentMounted();
      },

      /**
       * This is the method to use as componentDidMount is already
       * defined by Lite.
       */
      onComponentMounted : function() {},
    }

  var specs = mergeObjects(Lite.EventController(_evListenerComponent),
                           customClass);
  return React.createClass(specs);
};

