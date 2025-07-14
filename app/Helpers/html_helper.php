<?php

function getIcon($text) {
    $text_class = $text;
    switch($text) {
        case 'edit':
            $text = '<span class="text-icon">&#x270E;</span>';
            break;
        case 'delete':
            $text = '<svg width="10px" height="10px" viewBox="0 0 15 15" version="1.1" id="waste-basket" xmlns="http://www.w3.org/2000/svg">
  <path d="M12.41,5.58l-1.34,8c-0.0433,0.2368-0.2493,0.4091-0.49,0.41H4.42c-0.2407-0.0009-0.4467-0.1732-0.49-0.41l-1.34-8&#xA;&#x9;C2.5458,5.3074,2.731,5.0506,3.0035,5.0064C3.0288,5.0023,3.0544,5.0002,3.08,5h8.83c0.2761-0.0036,0.5028,0.2174,0.5064,0.4935&#xA;&#x9;C12.4168,5.5225,12.4146,5.5514,12.41,5.58z M13,3.5C13,3.7761,12.7761,4,12.5,4h-10C2.2239,4,2,3.7761,2,3.5S2.2239,3,2.5,3H5V1.5&#xA;&#x9;C5,1.2239,5.2239,1,5.5,1h4C9.7761,1,10,1.2239,10,1.5V3h2.5C12.7761,3,13,3.2239,13,3.5z M9,3V2H6v1H9z" fill="#2675b9"/>
</svg>';
            break;
        case 'info':
            $text = '&#8505;';
            break;
        case 'excel':
            $text = '<img src="assets/img/icon-excel.png">';
            break;
        case 'copy':
            $text = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="10px" height="10px" viewBox="0 0 92 92" enable-background="new 0 0 92 92" xml:space="preserve">
<path id="XMLID_1525_" d="M46,21.9c-0.7-0.7-1.6-0.9-2.5-0.9H13.6c-2,0-4.6,1.2-4.6,3.1v63.3c0,2,2.6,4.5,4.6,4.5H61
	c2,0,3-2.6,3-4.5V41.8c0-0.9-0.1-1.7-0.8-2.4L46,21.9z M55.9,42H43V29.1L55.9,42z M17,84V28h19v17.2c0,2,1.8,3.8,3.8,3.8H57v35H17z
	 M82.4,18L65,0.6C64.4,0.1,63.8,0,63,0H33.1C31.4,0,30,0.9,30,2.5v9.6c0,1.7,1.3,3,3,3c1.7,0,3-1.4,3-3V6h21v16.9
	c0,1.7,1.4,3.1,3.1,3.1H77v37h-4.1c-1.7,0-3,1.3-3,3s1.4,3,3,3h7.6c1.7,0,2.5-1.5,2.5-3.2V20.2C83,19.4,82.9,18.6,82.4,18z M63,7
	l12.9,13H63V7z" fill="#2675b9"/>
</svg>';
            break;
        case 'print':
            $text = '<svg width="10px" height="10px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
  <path fill="#2675b9" d="M21 8h-1V2c0-1.1-.9-2-2-2H6C4.9 0 4 .9 4 2v6H3c-1.65 0-3 1.35-3 3v6c0 1.65 1.35 3 3 3h2v2c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2v-2h2c1.65 0 3-1.35 3-3v-6c0-1.65-1.35-3-3-3zM6 2.5c0-.276.224-.5.5-.5h11c.276 0 .5.224.5.5V8H6V2.5zm11 19c0 .276-.224.5-.5.5h-9c-.276 0-.5-.224-.5-.5V17h10v4.5zm5-4.5c0 .54-.46 1-1 1h-2v-1c0-1.1-.9-2-2-2H7c-1.1 0-2 .9-2 2v1H3c-.54 0-1-.46-1-1v-6c0-.54.46-1 1-1h18c.54 0 1 .46 1 1v6z"/>
  <circle fill="#2675b9" cx="5" cy="13" r="1"/>
</svg>';
            break;
        case 'email':
            $text = '<span class="text-icon">@</span>';
            break;
    }

    return '<span class="text-icon icon-'.$text_class.'">'.$text.'</span>';
}
