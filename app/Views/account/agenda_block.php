<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h2>Agenda: Automatischen Kauf blockieren</h2>
<p>Klicke auf ein Datum, um es zu sperren oder entsperren.</p>

<!-- Kalender -->
<div id="calendar"></div>

<!-- CSS -->
<link rel="stylesheet" href="<?= base_url('css/calendar-gc.css') ?>">

<!-- JS -->
<script src="<?= base_url('js/calendar-gc.js') ?>"></script>

<div id="calendar" class="d-none"></div>




<script>
    $(function (e) {
        var today = new Date();
        var events = [];
        var calendar;

        calendar = $("#calendar").calendarGC({
            dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            dayBegin: 1,
            prevIcon: '&#x3c;',
            nextIcon: '&#x3e;',
            onPrevMonth: function (pickedDate) {
                load_events(new Date(pickedDate));
            },
            onNextMonth: function (pickedDate) {
                load_events(new Date(pickedDate));
            },
            events: events,
            onclickDate: function (e, data) {
            }
        });

        function alertPopup(message, type) {
            var modal;

            modal = $('<div class="modal fade" tabindex="-1" role="dialog">' +
                '<div class="modal-dialog" role="document">' +
                '<div class="modal-content bg-'+type+' bg-opacity-100">' +
                '<div class="modal-body">' +
                message +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>');

            $('body').append(modal);
            modal.modal('show');
            modal.on('hidden.bs.modal', function () {
                modal.remove();
            });
        }

        function load_events(pickedDate) {
            events = [];

            var segment_month = "";

            var seconds = Math.floor(pickedDate.getTime() / 1000);
            $.ajax({
                url: '<?=base_url('agenda/blocked_events');?>',
                type: 'GET',
                data: 'ts=' + seconds,
                dataType: 'json',
                success: function (data) {
                    $("#custom-teacher-color-styles").html(data['styles']);
                    this.segment_month = data['segment_month'];
                    $.each(data['events'], function(index, value) {
                        var d = new Date();
                        var newDate = new Date(d.getFullYear(), d.getMonth(), 1);
                        const dateParts = value['eventDate'].split(", ");
                        const year = parseInt(dateParts[0]);
                        const month = parseInt(dateParts[1]) - 1; // Month is zero-based in JavaScript (0 - 11)
                        const day = parseInt(dateParts[2]);
                        const jsDate = new Date(year, month, day);
                        value['date'] = jsDate;

                        events.push({
                            date: value['date'],
                            eventName: value['eventName'],
                            className: value['className'],
                            dateColor: value['dateColor'],
                            onclick(e, data) {

                            },
                        });
                    });
                },
                error: function (xhr, textStatus, errorThrown) {
                    //alertPopup(errorThrown, 'danger');
                },
                complete: function() {
                    calendar.setEvents(events);
                    calendar.setDate(pickedDate);
                    $('#calendar').removeClass('d-none');

                    /* has checkbox with class check-if-change add eventlistener and remove js-remove */
                    var checkboxes = $('.check-if-change');
                    checkboxes.change(function (event) {
                        var log_form_container = $(event.target).closest('.logo-form-container');

                        console.log($(log_form_container));
                        event.preventDefault();
                        var form = $(event.target).closest('form');
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: form.serialize(),
                            dataType: 'json',
                            success: function(response) {
                                alertPopup(response['message'], response['type']);
                                log_form_container.find('img').replaceWith(response['icon']);
                            },
                            error: function(xhr, status, error) {
                                alertPopup(error.message, 'danger');
                            }
                        });
                    });


                    if ($('.today').length) {
                        // Scroll to .today class smoothly
                        $('html, body').animate({
                            scrollTop: $('.today').offset().top - $('header').height() - $('.gc-calendar-header').height() - $('.gc-calendar-footer').height() - 10
                        }, 1000);
                    }


                    $("#segment-month").html(this.segment_month);
                }
            });
        }


        load_events(new Date());
    });

</script>



<style>
    .event-red {
        background-color: #dc3545 !important;
        color: white !important;
        padding: 0 4px;
    }

</style>


<?= $this->endSection() ?>
