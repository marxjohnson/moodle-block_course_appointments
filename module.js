document.body.className += ' yui-skin-sam';
YAHOO.util.Event.onDOMReady(function(){

    var Event = YAHOO.util.Event,
        Dom = YAHOO.util.Dom,
        dialog,
        calendar;

    var listeners = new Array();
    showBtn = Dom.get("show");
    dateInput = Dom.get("appointment_date");
    listeners.push(showBtn);
    listeners.push(dateInput);

    Event.on(listeners, "click", courseappointments_showcalendar);
    Event.on(dateInput, "focus", courseappointments_showcalendar);

    function courseappointments_showcalendar() {

        // Lazy Dialog Creation - Wait to create the Dialog, and setup document click listeners, until the first time the button is clicked.
        if (!dialog) {

            // Hide Calendar if we click anywhere in the document other than the calendar
            Event.on(document, "click", function(e) {
                var el = Event.getTarget(e);
                var dialogEl = dialog.element;
                if (el != dialogEl && !Dom.isAncestor(dialogEl, el) && el != showBtn && !Dom.isAncestor(showBtn, el)&& el != dateInput && !Dom.isAncestor(dateInput, el)) {
                    dialog.hide();
                }
            });

            function resetHandler() {
                // Reset the current calendar page to the select date, or
                // to today if nothing is selected.
                var selDates = calendar.getSelectedDates();
                var resetDate;

                if (selDates.length > 0) {
                    resetDate = selDates[0];
                } else {
                    resetDate = calendar.today;
                }

                calendar.cfg.setProperty("pagedate", resetDate);
                calendar.render();
            }

            function closeHandler() {
                dialog.hide();
            }

            dialog = new YAHOO.widget.Dialog("calcontainer", {
                visible:false,
                context:["appointment_date", "tr", "bl"],
                buttons:[ {text:"Reset", handler: resetHandler, isDefault:true}, {text:"Close", handler: closeHandler}],
                draggable:false,
                close:true
            });
            dialog.setHeader('Pick A Date');
            dialog.setBody('<div id="cal"></div>');
            dialog.render(document.body);

            dialog.showEvent.subscribe(function() {
                if (YAHOO.env.ua.ie) {
                    // Since we're hiding the table using yui-overlay-hidden, we
                    // want to let the dialog know that the content size has changed, when
                    // shown
                    dialog.fireEvent("changeContent");
                }
            });
        }

        // Lazy Calendar Creation - Wait to create the Calendar until the first time the button is clicked.
        if (!calendar) {
            var now = new Date();
            var selected = new Date(dateInput.value);
            calendar = new YAHOO.widget.Calendar("cal", {
                iframe:false,          // Turn iframe off, since container has iframe support.
                hide_blank_weeks:true,  // Enable, to demonstrate how we handle changing height, using changeContent
                mindate: now,
                selected: (selected.getMonth()+1)+'/'+selected.getDate()+'/'+selected.getFullYear()
            });
            calendar.render();

            calendar.selectEvent.subscribe(function() {
                if (calendar.getSelectedDates().length > 0) {

                    var selDate = calendar.getSelectedDates()[0];

                    // Pretty Date Output, using Calendar's Locale values: Friday, 8 February 2008                   
                    var dStr = selDate.getDate();
                    var mStr = calendar.cfg.getProperty("MONTHS_SHORT")[selDate.getMonth()];
                    var yStr = selDate.getFullYear();

                    Dom.get("appointment_date").value = dStr + " " + mStr + " " + yStr;
                } else {
                    Dom.get("appointment_date").value = "";
                }
                dialog.hide();
            });

            calendar.renderEvent.subscribe(function() {
                // Tell Dialog it's contents have changed, which allows
                // container to redraw the underlay (for IE6/Safari2)
                dialog.fireEvent("changeContent");
            });
        }

        var seldate = calendar.getSelectedDates();

        if (seldate.length > 0) {
            // Set the pagedate to show the selected date if it exists
            calendar.cfg.setProperty("pagedate", seldate[0]);
            calendar.render();
        }

        dialog.show();
    }
});
