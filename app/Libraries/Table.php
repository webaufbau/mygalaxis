<?php

namespace App\Libraries;

class Table {

    public $search = false;

    public $temp = null;

    public $table_id = 'table_table_data';

    /**
     * Data for table rows
     *
     * @var array
     */
    public $rows = array();

    /**
     * Data for table heading
     *
     * @var array
     */
    public $heading = array();

    /**
     * Whether or not to automatically create the table header
     *
     * @var bool
     */
    public $auto_heading = TRUE;

    /**
     * Table caption
     *
     * @var string
     */
    public $caption = NULL;

    /**
     * Table layout template
     *
     * @var array
     */
    public $template = NULL;

    /**
     * Newline setting
     *
     * @var string
     */
    public $newline = "\n";

    /**
     * Contents of empty cells
     *
     * @var string
     */
    public $empty_cells = '';

    /**
     * Callback for custom table layout
     *
     * @var function
     */
    public $function = NULL;

    /**
     * Set the template from the table config file if it exists
     *
     * @param array $config (default: array())
     * @return    void
     */
    public function __construct($config = array()) {
        // initialize config
        foreach ($config as $key => $val) {
            $this->template[$key] = $val;
        }

        log_message('info', 'Table Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Set the template
     *
     * @param array $template
     * @return    bool
     */
    public function set_template($template) {
        if (!is_array($template)) {
            return FALSE;
        }

        $this->template = $template;
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Set the table heading
     *
     * Can be passed as an array or discreet params
     *
     * @param mixed
     * @return    CI_Table
     */
    public function set_heading($args = array()) {
        $this->heading = $this->_prep_args(func_get_args());
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Set columns. Takes a one-dimensional array as input and creates
     * a multi-dimensional array with a depth equal to the number of
     * columns. This allows a single array with many elements to be
     * displayed in a table that has a fixed column count.
     *
     * @param array $array
     * @param int $col_limit
     * @return    array
     */
    public function make_columns($array = array(), $col_limit = 0) {
        if (!is_array($array) or count($array) === 0 or !is_int($col_limit)) {
            return FALSE;
        }

        // Turn off the auto-heading feature since it's doubtful we
        // will want headings from a one-dimensional array
        $this->auto_heading = FALSE;

        if ($col_limit === 0) {
            return $array;
        }

        $new = array();
        do {
            $temp = array_splice($array, 0, $col_limit);

            if (count($temp) < $col_limit) {
                for ($i = count($temp); $i < $col_limit; $i++) {
                    $temp[] = '&nbsp;';
                }
            }

            $new[] = $temp;
        } while (count($array) > 0);

        return $new;
    }

    // --------------------------------------------------------------------

    /**
     * Set "empty" cells
     *
     * Can be passed as an array or discreet params
     *
     * @param mixed $value
     * @return    CI_Table
     */
    public function set_empty($value) {
        $this->empty_cells = $value;
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Add a table row
     *
     * Can be passed as an array or discreet params
     *
     * @param mixed
     * @return    CI_Table
     */
    public function add_row($args = array()) {
        $this->rows[] = $this->_prep_args(func_get_args());
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Prep Args
     *
     * Ensures a standard associative array format for all cell data
     *
     * @param array
     * @return    array
     */
    protected function _prep_args($args) {
        // If there is no $args[0], skip this and treat as an associative array
        // This can happen if there is only a single key, for example this is passed to table->generate
        // array(array('foo'=>'bar'))
        if (isset($args[0]) && count($args) === 1 && is_array($args[0]) && !isset($args[0]['data'])) {
            $args = $args[0];
        }

        foreach ($args as $key => $val) {
            if (is_null($val)):
                unset($args[$key]);
            endif;
            is_array($val) or $args[$key] = array('data' => $val);
        }

        return $args;
    }

    // --------------------------------------------------------------------

    /**
     * Add a table caption
     *
     * @param string $caption
     * @return    CI_Table
     */
    public function set_caption($caption) {
        $this->caption = $caption;
        return $this;
    }

    // --------------------------------------------------------------------

    // --------------------------------------------------------------------

    /**
     * Set table data from a database result object
     *
     * @param CI_DB_result $object Database result object
     * @return    void
     */
    protected function _set_from_db_result($object) {
        // First generate the headings from the table column names
        if ($this->auto_heading === TRUE && empty($this->heading)) {
            $this->heading = $this->_prep_args($object->list_fields());
        }

        foreach ($object->result_array() as $row) {
            $this->rows[] = $this->_prep_args($row);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set table data from an array
     *
     * @param array $data
     * @return    void
     */
    protected function _set_from_array($data) {
        if ($this->auto_heading === TRUE && empty($this->heading)) {
            $this->heading = $this->_prep_args(array_shift($data));
        }

        foreach ($data as &$row) {
            $this->rows[] = $this->_prep_args($row);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Compile Template
     *
     * @return    void
     */
    /*protected function _compile_template()
    {
        if ($this->template === NULL)
        {
            $this->template = $this->_default_template();
            return;
        }

        $this->temp = $this->_default_template();
        foreach (array('table_open', 'thead_open', 'thead_close', 'heading_row_start', 'heading_row_end', 'heading_cell_start', 'heading_cell_end', 'tbody_open', 'tbody_close', 'row_start', 'row_end', 'cell_start', 'cell_end', 'row_alt_start', 'row_alt_end', 'cell_alt_start', 'cell_alt_end', 'table_close') as $val)
        {
            if ( ! isset($this->template[$val]))
            {
                $this->template[$val] = $this->temp[$val];
            }
        }
    }*/

    // --------------------------------------------------------------------

    /**
     * Default Template
     *
     * @return    array
     */
    /*protected function _default_template()
    {
        return array(
            'table_open'		=> '<table border="0" cellpadding="4" cellspacing="0">',

            'thead_open'		=> '<thead>',
            'thead_close'		=> '</thead>',

            'heading_row_start'	=> '<tr>',
            'heading_row_end'	=> '</tr>',
            'heading_cell_start'	=> '<th>',
            'heading_cell_end'	=> '</th>',

            'tbody_open'		=> '<tbody>',
            'tbody_close'		=> '</tbody>',

            'row_start'		=> '<tr>',
            'row_end'		=> '</tr>',
            'cell_start'		=> '<td>',
            'cell_end'		=> '</td>',

            'row_alt_start'		=> '<tr>',
            'row_alt_end'		=> '</tr>',
            'cell_alt_start'	=> '<td>',
            'cell_alt_end'		=> '</td>',

            'table_close'		=> '</table>'
        );
    }*/


    var $row_attr = array();
    var $footer = array();

    function add_row_attr($attributes = array()) {
        $this->row_attr[] = $this->_prep_args($attributes);
    }

    public function reset() {
        $this->clear();
        $this->row_attr = array();
        $this->footer = array();
    }

    /**
     * Set the table footer
     *
     * Can be passed as an array or discreet params
     *
     * @access    public
     * @param mixed
     * @return    void
     */
    public function set_footer() {
        $args = func_get_args();
        $this->footer = $this->_prep_args($args);
    }

    // --------------------------------------------------------------------

    /**
     * Generate the table
     *
     * @access  public
     * @param mixed
     * @return  string
     */
    function generate($table_data = NULL) {
        // The table data can optionally be passed to this function
        // either as a database result object or an array
        if (!is_null($table_data)) {
            if (is_object($table_data)) {
                $this->_set_from_object($table_data);
            } elseif (is_array($table_data)) {
                $set_heading = (count($this->heading) == 0 and $this->auto_heading == FALSE) ? FALSE : TRUE;
                $this->_set_from_array($table_data, $set_heading);
            }
        }

        // Is there anything to display?  No?  Smite them!
        if (count($this->heading) == 0 and count($this->rows) == 0) {
            return 'Undefined table data';
        }

        // Compile and validate the template date
        $this->_compile_template();

        // set a custom cell manipulation function to a locally scoped variable so its callable
        $function = $this->function;

        // Build the table!
        $out = '';

        // Erzeugen des Suchfeldes, wenn die Suche aktiviert ist
        if ($this->search) {
            $out .= $this->generateSearchHtml();
        }

        $out .= $this->template['table_open'];
        $out .= $this->newline;

        // Add any caption here
        if ($this->caption) {
            $out .= $this->newline;
            $out .= '<caption>' . $this->caption . '</caption>';
            $out .= $this->newline;
        }

        // Is there a table heading to display?
        if (count($this->heading) > 0) {
            $out .= $this->template['thead_open'];
            $out .= $this->newline;
            $out .= $this->template['heading_row_start'];
            $out .= $this->newline;

            foreach ($this->heading as $heading) {
                $temp = $this->template['heading_cell_start'];

                foreach ($heading as $key => $val) {
                    if ($key != 'data') {
                        $temp = str_replace('<th', "<th $key='$val'", $temp);
                    }
                }

                $out .= $temp;
                $out .= isset($heading['data']) ? $heading['data'] : '';
                $out .= $this->template['heading_cell_end'];
            }

            $out .= $this->template['heading_row_end'];
            $out .= $this->newline;
            $out .= $this->template['thead_close'];
            $out .= $this->newline;
        }

        // Build the table rows
        if (count($this->rows) > 0) {
            $out .= $this->template['tbody_open'];
            $out .= $this->newline;

            $i = 1;
            $cnt = 0;
            foreach ($this->rows as $row) {
                if (!is_array($row)) {
                    break;
                }

                // We use modulus to alternate the row colors
                $name = (fmod($i++, 2)) ? '' : 'alt_';

                //nvg
                $tr_end = '';
                if (isset($this->row_attr[$cnt]) && is_array($this->row_attr[$cnt])) {
                    foreach ($this->row_attr[$cnt] as $attr => $attrvalue) {
                        $value = $attrvalue['data'];
                        $tr_end .= ' ' . $attr . '="' . $value . '" ';
                    }
                } else {

                }
                $tr_end .= '>';
                $cnt++;

                $out .= ($this->template['row_' . $name . 'start'] . $tr_end);
                $out .= $this->newline;

                foreach ($row as $cell) {
                    $temp = $this->template['cell_' . $name . 'start'];

                    foreach ($cell as $key => $val) {
                        if ($key != 'data') {

                            $temp = str_replace('<td', "<td $key='$val'", $temp);
                        }
                    }

                    $cell = isset($cell['data']) ? $cell['data'] : '';
                    $out .= $temp;

                    if ($cell === "" or $cell === NULL) {
                        $out .= $this->empty_cells;
                    } else {
                        if ($function !== FALSE && is_callable($function)) {
                            $out .= call_user_func($function, $cell);
                        } else {
                            if(is_object($cell) && method_exists($cell, 'toString')) {
                                $cell = $cell->toString();
                            } elseif(is_object($cell)) {
                                $cell = $cell->__toString();
                            }
                            $out .= $cell;
                        }
                    }

                    $out .= $this->template['cell_' . $name . 'end'];
                }

                $out .= $this->template['row_' . $name . 'end'];
                $out .= $this->newline;
            }

            $out .= $this->template['tbody_close'];
            $out .= $this->newline;
        }

        /**
         * RHS -> added tfoot codes
         * */
        // Is there a table footer to display?
        if (count($this->footer) > 0) {
            $out .= $this->template['tfoot_open'];
            $out .= $this->newline;
            $out .= $this->template['footer_row_start'];
            $out .= $this->newline;
            foreach ($this->footer as $footer) {
                $temp = $this->template['footer_cell_start'];

                $colspan = $this->_calculateColspan($footer);

                foreach ($footer as $key => $val) {
                    if ($key != 'data') {
                        $temp = str_replace('<th', "<th $key='$val'", $temp);
                    }
                }

                $temp = str_replace('<th', "<th colspan='$colspan'", $temp);

                $out .= $temp;
                $out .= isset($footer['data']) ? $footer['data'] : '';
                $out .= $this->template['footer_cell_end'];
            }
            $out .= $this->template['footer_row_end'];
            $out .= $this->newline;
            $out .= $this->template['tfoot_close'];
            $out .= $this->newline;
        } else {
            $colspan = $this->_calculateColspan();

            // If no footer is set, dynamically generate a footer with the count of entries
            $entryCount = count($this->rows);

            $out .= $this->template['tfoot_open'];
            $out .= $this->newline;
            $out .= $this->template['footer_row_start'];
            $out .= $this->newline;

            $temp = $this->template['footer_cell_start'];
            $temp = str_replace('<th', "<th colspan='$colspan'", $temp);

            $out .= $temp;
            if($entryCount == 1) {
                $out .= $entryCount . ' Eintrag';
            } else {
                $out .= $entryCount . ' Einträge';
            }

            $out .= $this->template['footer_cell_end'];

            $out .= $this->template['footer_row_end'];
            $out .= $this->newline;
            $out .= $this->template['tfoot_close'];
            $out .= $this->newline;
        }

        $out .= $this->template['table_close'];

        // Clear table class properties before generating the table
        $this->clear();

        return $out;
    }

    // Funktion, um das Suchfeld und JavaScript zu generieren
    protected function generateSearchHtml() {
        $searchHtml = '<input type="text" id="tableSearchInput" placeholder="Suche..." class="mb-2">';
        $searchHtml .= '<script>
            document.getElementById("tableSearchInput").addEventListener("keyup", function() {
                var input, filter, table, tr, td, i, j, txtValue, found;
                input = document.getElementById("tableSearchInput");
                filter = input.value.toUpperCase();
                table = document.getElementById("'.$this->table_id.'");
                tr = table.getElementsByTagName("tr");
        
                for (i = 0; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td");
                    found = false;
        
                    for (j = 0; j < td.length; j++) {
                        if (td[j]) {
                            txtValue = td[j].textContent || td[j].innerText;
                            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                                found = true;
                                break;
                            }
                        }
                    }
        
                    if (found) {
                        tr[i].style.display = "";
                    } else if (tr[i].getElementsByTagName("th").length == 0) {
                        tr[i].style.display = "none";
                    }
                }
            });
        </script>';

        return $searchHtml;
    }

    /**
     * Calculate colspan based on the number of header columns
     *
     * @param array $footer
     * @return int
     */
    protected function _calculateColspan($footer=[]) {
        $headerCount = count($this->heading);
        $footerCount = count($footer);

        // If the counts are different, calculate the difference
        $colspan = max(1, $headerCount - $footerCount + 1);

        return $colspan;
    }

    /**
     * Clears the table arrays.  Useful if multiple tables are being generated
     *
     * @access    public
     * @return    void
     */
    function clear() {
        $this->rows = array();
        $this->heading = array();
        $this->footer = array();
        $this->auto_heading = TRUE;

        $this->rows = array();
        $this->heading = array();
        $this->auto_heading = TRUE;
        $this->caption = NULL;
        return $this;
    }


    /**
     * Compile Template
     *
     * @access  private
     * @return  void
     */
    function _compile_template() {
        if ($this->template == NULL) {
            $this->template = $this->_default_template();
            return;
        }

        $this->temp = $this->_default_template();
        foreach (array(
                     'table_open',
                     'thead_open',
                     'thead_close',
                     'tfoot_open',
                     'tfoot_close',
                     'heading_row_start',
                     'heading_row_end',
                     'heading_cell_start',
                     'heading_cell_end',
                     'tfoot_open',
                     'tfoot_close',
                     'footer_row_start',
                     'footer_row_end',
                     'footer_cell_start',
                     'footer_cell_end',
                     'tbody_open',
                     'tbody_close',
                     'row_start',
                     'row_end',
                     'cell_start',
                     'cell_end',
                     'row_alt_start',
                     'row_alt_end',
                     'cell_alt_start',
                     'cell_alt_end',
                     'table_close'
                 ) as $val) {
            if (!isset($this->template[$val])) {
                $this->template[$val] = $this->temp[$val];
            }
        }
    }

    /**
     * Default Template
     *
     * RHS-> updated to support table footers
     *
     * @access    private
     * @return    void
     */
    function _default_template() {
        return array(
            'table_open' => '<table border="0" cellpadding="4" cellspacing="0" id="'.$this->table_id.'">',
            'thead_open' => '<thead>',
            'thead_close' => '</thead>',
            'heading_row_start' => '<tr>',
            'heading_row_end' => '</tr>',
            'heading_cell_start' => '<th>',
            'heading_cell_end' => '</th>',
            'tfoot_open' => '<tfoot>',
            'tfoot_close' => '</tfoot>',
            'footer_row_start' => '<tr>',
            'footer_row_end' => '</tr>',
            'footer_cell_start' => '<th>',
            'footer_cell_end' => '</th>',
            'tbody_open' => '<tbody>',
            'tbody_close' => '</tbody>',
            'row_start' => '<tr',
            'row_end' => '</tr>',
            'cell_start' => '<td>',
            'cell_end' => '</td>',
            'row_alt_start' => '<tr',
            'row_alt_end' => '</tr>',
            'cell_alt_start' => '<td>',
            'cell_alt_end' => '</td>',
            'table_close' => '</table>'
        );
    }

}
