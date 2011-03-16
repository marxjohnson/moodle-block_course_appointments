// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Defines the Javascript module for the course appointments block
 * 
 * @package blocks
 * @subpackage course_appointments
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */ 
 
M.block_course_appointments = {

    /**
     * Stores the calendar instance for access between runs of showcalendar()
     * @var {Object} cal
     */
    cal: false,
       
    /**
     * Stores the node for the date field so we can access and set the currently
     * selected date.
     *  
     * @var {Object} appointmentdate
     */
    appointmentdate: {},
    
    /**
     * Module init function. Gets the button and date fields, and attaches event 
     * listers to them.
     * 
     * @param {Object} Y
     */
    init: function(Y) {
        Y.one('body').addClass('yui-skin-sam');
        var show = Y.one('#block_courseappointments_show');
        this.appointmentdate = Y.one('#block_courseappointments_appointmentdate');
        Y.on('click', this.showcalendar, Array(show, this.appointmentdate));
        Y.on('focus', this.showcalendar, this.appointmentdate);
    },
    
    /**
     * Responds to click events on the calendar button or date field, and displays the calendar.
     * 
     * @param {Object} e
     */
    showcalendar: function(e){
        // This is a tad ugly. However YUI3 Doesn't have a calendar widget, so we need to use YUI2.
        
        YUI().use('yui2-calendar', function(Y){
            // Set up access to the YUI2 namespace, the block and the currently seelected date.
            var YAHOO = Y.YUI2;
            var now = new Date();
            block = M.block_course_appointments;
            var selected = new Date(block.appointmentdate.value);
            // If this is the first time the event has run, we'll need to build and render the calendar.
            if (!block.cal) {
                // Set up the Calendar 
                block.cal = new YAHOO.widget.Calendar('block_courseappointments_calendarcontainer', {
                    iframe: false, // Turn iframe off, since container has iframe support.
                    hide_blank_weeks: true, // Enable, to demonstrate how we handle changing height, using changeContent
                    mindate: now,
                    selected: (selected.getMonth() + 1) + '/' + selected.getDate() + '/' + selected.getFullYear()
                });
                // Add an event listener to set the value of the date field and hide the calendar, when a date 
                // is selected.
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
                // The calendar's container is absolutely positioned so that it overlays the block itself.
                // This causes a bit of an issue if the calendar overflows the block region, so we'll extend
                // the region to make sure it fits.
                block.cal.renderEvent.subscribe(function(){
                    var region = Y.one('.block_course_appointments').ancestor('.region-content');
                    region.setStyle('height', parseInt(region.getStyle('height').replace('px', '')) + 185);
                });
                block.cal.render();
            } else {
                // If we've already rendered the calendar, we just need to display it.
                block.cal.show();
            }
            // Add an event listener to the document to hide the calendar if the user clicks outside the
            // calendar's container, or the form row with the date controls on.
            Y.on('click', function(e){
                if (e.target.ancestor('#block_courseappointments_calendarcontainer', true) == null &&
                e.target.ancestor('#block_courseappointments_daterow', true) == null) {
                    M.block_course_appointments.cal.hide();
                }
            }, document);
        });
    }
}
