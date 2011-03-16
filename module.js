
M.block_course_appointments = {
    
    Y: {},

    cal: false,
       
    appointmentdate: {},
    
    init: function(Y) {
        Y.one('body').addClass('yui-skin-sam');
        this.show = Y.one('#block_courseappointments_show');
        this.appointmentdate = Y.one('#block_courseappointments_appointmentdate');
        Y.on('click', this.showcalendar, Array(this.show, this.appointmentdate));
        Y.on('focus', this.showcalendar, this.appointmentdate);
        this.Y = Y;
    },
    
    showcalendar: function(e){
        YUI().use('dd-drag', 'yui2-calendar', function(Y){
            //This will make your YUI 2 code run unmodified
            var YAHOO = Y.YUI2;
            var now = new Date();
            block = M.block_course_appointments;
            var selected = new Date(block.appointmentdate.value);
            if (!block.cal) {
            
                block.cal = new YAHOO.widget.Calendar('block_courseappointments_calendarcontainer', {
                    iframe: false, // Turn iframe off, since container has iframe support.
                    hide_blank_weeks: true, // Enable, to demonstrate how we handle changing height, using changeContent
                    mindate: now,
                    selected: (selected.getMonth() + 1) + '/' + selected.getDate() + '/' + selected.getFullYear()
                });
                block.cal.selectEvent.subscribe(function(){
                    if (block.cal.getSelectedDates().length > 0) {
                    
                        var selDate = block.cal.getSelectedDates()[0];
                        
                        // Pretty Date Output, using Calendar's Loblock.cale values: Friday, 8 February 2008                   
                        var dStr = selDate.getDate();
                        var mStr = block.cal.cfg.getProperty("MONTHS_SHORT")[selDate.getMonth()];
                        var yStr = selDate.getFullYear();
                        block.appointmentdate.setAttribute('value', dStr + ' ' + mStr + ' ' + yStr);
                    }
                    else {
                        block.appointmentdate.setAttribute('value', '');
                    }
                    block.cal.hide();
                });
                block.cal.renderEvent.subscribe(function(){
                    var region = Y.one('.block_course_appointments').ancestor('.region-content');
                    region.setStyle('height', parseInt(region.getStyle('height').replace('px', '')) + 185);
                });
                block.cal.render();
            }
            else {
                block.cal.show();
            }
            Y.on('click', function(e){
                if (e.target.ancestor('#block_courseappointments_calendarcontainer', true) == null &&
                e.target.ancestor('#block_courseappointments_daterow', true) == null) {
                    M.block_course_appointments.cal.hide();
                }
            }, document);
        });
    }
}
