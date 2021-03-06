<?php

/****************************************************
 * Questionnaire classes
 ***************************************************/
 
class formTable {
    public $table_id = 'ft';            // id of the table for the DOM
    public $action = '';                // page to submit the form
    public $method = 'post';            // get or post
    public $instructions = '';            // instructions to display above the form
    public $questionList = array();        // array of formElement objects
    public $submit_text = '';            // text for submit button
    public $buttons;                    // array of button values => onclick scripts
    public $button_location = 'bottom';    // location of button(s): bottom, top or both
    public $title = false;                // title of form to put in <th>
    public $enctype = 'application/x-www-form-urlencoded';
    
    
    function set_table_id($x) { $this->table_id = $x; }
    function get_table_id() { return $this->table_id; }
    function set_action($x) { $this->action = $x; }
    function get_action() { return $this->action; }
    function set_method($x) { $this->method = (strtolower($x) == 'get') ? 'get' : 'post'; }
    function get_method() { return $this->method; }
    function set_instructions($x) { $this->instructions = $x; }
    function get_instructions() { return $this->instructions; }
    function set_questionList($x) { $this->questionList = $x; }
    function get_questionList() { return $this->questionList; }
    function add_question($x) { array_push($this->questionList, $x); }
    function set_submit_text($x) { $this->submit_text = $x; }
    function get_submit_text() { return $this->submit_text; }
    function set_buttons($x) { $this->buttons = $x; }
    function get_buttons() { return $this->buttons; }
    function set_button_location($x) { $this->button_location = $x; }
    function get_button_location() { return $this->button_location; }
    function set_title($x) { $this->title = $x; }
    function get_title() { return $this->title; }
    function set_enctype($x) { $this->enctype = ('multipart/form-data' == $x) ? $x : 'application/x-www-form-urlencoded'; }
    function get_enctype() { return $this->enctype; }
    
    function print_instructions() {
        if ($this->instructions != '') echo "<p class='instructions'>" , $this->instructions , "</p>" . PHP_EOL;
    }
    
    function print_form() {
        $this->print_instructions();

        echo "<form action='{$this->action}' 
            method='{$this->method}' 
            enctype='{$this->enctype}' 
            id='{$this->table_id}_form'>" . PHP_EOL;
        
        // top submit buttons    
        if (in_array($this->button_location, array('top', 'both'))) { 
            echo "<div class='buttons'>", PHP_EOL;
            if (!empty($this->submit_text)) {
                echo "    <input type='submit' value='", $this->submit_text, "' />", PHP_EOL;
            }
            if (!empty($this->buttons)) {
                foreach ($this->buttons as $v => $onclick) {
                    echo "    <input type='button' value='$v' onclick='$onclick' />", PHP_EOL;
                    //echo "    <button onclick='$onclick'>$v</button>", PHP_EOL;
                }
            }
            echo "</div>" , PHP_EOL . PHP_EOL;
        }
        
        // start table
        echo "<table class='questionnaire' id='", $this->table_id, "'>" . PHP_EOL;
        
        // optional title
        if ($this->title) {
            echo '    <thead><tr><th colspan="100">'.$this->title.'</th></tr></thead>', PHP_EOL;
        }
        
        echo '    <tbody>' . PHP_EOL;
        
        // list of questions
        foreach ($this->questionList as $q) {
            $q->print_formLine();
        }
        echo '    </tbody>' . PHP_EOL;
        echo "</table>", PHP_EOL;
        
        // bottom submit buttons    
        if (in_array($this->button_location, array('bottom', 'both'))) { 
            echo "<div class='buttons'>", PHP_EOL;
            if (!empty($this->submit_text)) {
                echo "    <input type='submit' value='", $this->submit_text, "' />", PHP_EOL;
            }
            if (!empty($this->buttons)) {
                foreach ($this->buttons as $v => $onclick) {
                    echo "    <input type='button' value='$v' onclick='$onclick' />", PHP_EOL;
                    //echo "    <button onclick='$onclick'>$v</button>", PHP_EOL;
                }
            }
            echo "</div>" , PHP_EOL . PHP_EOL;
        }
        echo "</form>" , PHP_EOL . PHP_EOL;
    }
    
}
 
class questionnaire extends formTable {
    public $id;
    public $name = '';
    public $resname = '';
    public $options;
    public $type;

    function __construct($i) {
        $this->id = $i;
        
        if (is_numeric($i) && $i>0) {
            // get questionnaire info from mysql table
            $this_quest = new myQuery('SELECT * FROM quest WHERE id=' . $this->id);
            $qInfo = $this_quest->get_assoc(0);
            
            // set options if radiopage
            if ($qInfo['questtype'] == 'radiopage') {
                // get radiorow options
                $query = new myQuery('SELECT opt_value, display FROM radiorow_options WHERE quest_id=' . $this->id . ' ORDER BY opt_order');
                $radiorow_options = array();
                foreach ($query->get_assoc() as $o) {
                    $radiorow_options[$o['opt_value']] = $o['display'];
                }
            }
            
            // set up questions
            $the_order = ($qInfo['quest_order'] == 'random') ? 'RAND()' : 'n';
            $questionData = new myQuery('SELECT * FROM question WHERE quest_id=' . $this->id . ' ORDER BY ' . $the_order);
            $questions = array();
            
            foreach ($questionData->get_assoc() as $qd) {
                // create new question object
                $qdtype = ($qd['type'] == 'text') ? 'input' : $qd['type'];
                $questions[$qd['id']] = new $qdtype('q' . $qd['id'], $qd['name']);
                $questions[$qd['id']]->set_question($qd['question']);
                
                // object-specific settings
                switch ($qd['type']) {
                    case 'countries':
                    case 'ranking':
                    case 'input':
                    case 'text':
                    case 'textarea':
                        break;
                    case 'select':
                    case 'radio':
                    case 'selectnum':
                        // get options
                        $optData = new myQuery('SELECT opt_value, display FROM options WHERE q_id=' . $qd['id'] . ' ORDER BY opt_order');
                        $opt = $optData->get_assoc(false, 'opt_value', 'display');
                        if ('selectnum' == $qd['type']) {
                            $questions[$qd['id']]->set_options($opt, $qd['low_anchor'], $qd['high_anchor']);
                        } else {
                            $questions[$qd['id']]->set_options($opt);
                        }
                        break;
                    case 'radiorow':
                    case 'radiorev':
                        $questions[$qd['id']]->set_options($radiorow_options);
                        break;
                    case 'radioanchor':
                        $questions[$qd['id']]->set_options($qd['maxlength'],$qd['low_anchor'],$qd['high_anchor']);
                        break;
                    case 'datemenu':    
                        $questions[$qd['id']]->set_years($qd['low_anchor'], $qd['high_anchor']);
                        break;
                    case 'datepicker':    
                        $questions[$qd['id']]->set_years($qd['low_anchor'], $qd['high_anchor']);
                        break;
                }
                
                $questions[$qd['id']]->set_question($qd['question']);
            }
            
            $this->set_questionList($questions);
            
            // set questionnaire info
            $this->set_name($qInfo['name']);
            $this->set_type($qInfo['questtype']);
            $this->set_instructions($qInfo['instructions']);

            if ($qInfo['questtype'] == 'radiopage') {
                $this->set_options($radiorow_options);
            }
        }
    }
    
    function check_exists() {
        if ($this->type == '') { return false; }
        return true;
    }
    
    function check_eligible() {
        $user_sex = $_SESSION['sex'];
        $user_sexpref = $_SESSION['sexpref'];
        $user_age = $_SESSION['age'];
        
        $query = new myQuery('SELECT lower_age, upper_age, sex, sexpref FROM quest WHERE id=' . $this->id);
        $expinfo = $query->get_one_array();
        
        $eligible = true;
        
        $eligible = $eligible && (is_null($expinfo['lower_age']) || $user_age >= $expinfo['lower_age']);
        $eligible = $eligible && (is_null($expinfo['upper_age']) || $user_age <= $expinfo['upper_age']);
        $eligible = $eligible && (is_null($expinfo['sex']) || $expinfo['sex'] == 'both' || $user_sex == $expinfo['sex']);
        $eligible = $eligible && (is_null($expinfo['sexpref']) || $user_sexpref == $expinfo['sexpref']);
        
        return $eligible;
    }
    
    function set_id($x) { $this->id = $x; }
    function get_id() { return $this->id; }
    function set_name($x) { $this->name = $x; }
    function get_name() { return $this->name; }
    function set_resname($x) { $this->resname = $x; }
    function get_resname() { return $this->resname; }
    function set_options($x) { $this->options = $x; }
    function get_options() { return $this->options; }
    function set_type($x) { $this->type = $x; }
    function get_type() { return $this->type; }
    
    function get_option_row() {
        $radio_width = (MOBILE) ? round(100/count($this->options), 1) : round(50/count($this->options), 1);
        $opt_row =  "<tr class='radiorow_options'>";
        $opt_row .= (MOBILE) ? "" : "<th></th>";
        foreach ($this->options as $display) {
            $opt_row .= "<th style='width:{$radio_width}%'>$display</th>";
        }
        $opt_row .= "</tr>" . PHP_EOL;
        
        return $opt_row;
    }
    
    function print_form() {
        $this->print_instructions();
        
        echo "<form action='{$this->action}' method='{$this->method}' id='quest_{$this->id}'>" . PHP_EOL;
        
        // hidden values
        echo "<input type='hidden' name='quest_id' id='quest_id' value='" , $this->id , "' />" . PHP_EOL;
        $starttime = ifEmpty($clean['starttime'], date('Y-m-d H:i:s'));
        echo "<input type='hidden' name='starttime' id='starttime' value='$starttime' />" . PHP_EOL;
        
        echo "<table class='questionnaire {$this->type}' id='qTable'>" . PHP_EOL;
        
        // questions
        $n = 0;
        $num_questions = count($this->questionList);
        foreach ($this->questionList as $q) {
            // print radiorow option row every 10 lines, but not if there are <5 questions left (unless there are <5 questions total)
            if ( ( ( (++$n%10) == 1 &&  $n < $num_questions - 5) || $n == 1) && !empty($this->options)) { echo $this->get_option_row(); }
            
            if (1 == $n) { echo "<tbody id='qTableBody'>", PHP_EOL; }  // start body after first header
            
            $q->print_formLine();
        }
        echo "</tbody></table>", PHP_EOL,
             "<div class='buttons'><input type='button' value='submit' onclick='submitQ({$this->id});' /></div>", PHP_EOL,
             "</form>" , PHP_EOL . PHP_EOL;
        
        // javascripts for ranking
        if ('ranking' == $this->type) {
            echo    '<script>', PHP_EOL,
                    '    $j(function() {', PHP_EOL,
                    '        $j("#qTableBody").sortable({update: function() { onReorder(); } });', PHP_EOL,
                    '        $j("#qTableBody").disableSelection();', PHP_EOL,
                    '        onReorder();', PHP_EOL,
                    '    });', PHP_EOL,
                    PHP_EOL,
                    '    function onReorder() {', PHP_EOL,
                    '        stripe("#qTableBody");', PHP_EOL,
                    '        $j("#qTableBody tr td.handle").each( function(i) { $j(this).text(i+1); } );', PHP_EOL,
                    '        var items = $j("#qTableBody tr");', PHP_EOL,
                    '        items.each( function(intIndex) {', PHP_EOL,
                    '            var q_id = "#q" + $j(this).attr("id").replace("row_","");', PHP_EOL,
                    '            $j(q_id).val(intIndex+1);', PHP_EOL,
                    '        });', PHP_EOL,
                    '    }', PHP_EOL,
                    '</script>', PHP_EOL . PHP_EOL;
        }
    }
}

class formElement {
    public $id = '';
    public $name = '';
    public $value = '';
    public $question = '';
    public $tip = '';
    public $eventHandlers = array();
    public $custom_input = '';
    public $required = false;
    
    function __construct($id, $variable_name, $current_value='') {
        $this->id = $id;
        $this->set_name($variable_name);
        $this->set_value($current_value);
    }
    
    function set_id($x) { $this->id = $x; }
    function get_id() { return $this->id; }
    function set_name($x) { $this->name = str_replace(' ', '_', $x); }
    function get_name() { return $this->name; }
    function set_value($x) { 
        $this->value = $x; 
        if (!is_array($this->value)) settype($this->value, "string"); 
    }
    function get_value() { return $this->value; }
    function set_question($x) { $this->question = $x; }
    function get_question() { return $this->question; }
    function set_eventHandlers($x) { $this->eventHandlers = $x; }
    function get_eventHandlers() { return $this->eventHandlers; }
    function set_tip($x) { $this->tip = $x; }
    function get_tip() { return $this->tip; }
    function set_custom_input($x) { $this->custom_input = $x; }
    function get_custom_input() { return $this->custom_input; }
    function set_required($x) { $this->required = (true == $x) ? true : false; }
    function get_required() { return $this->required; }
    
    function get_element() { 
        return $this->custom_input;
    }
    
    function print_formLine($editable=false) {
        echo "<tr title='{$this->tip}' id='{$this->id}_row'>" . PHP_EOL;    
        
        // display question cell
        echo "<td class='question'><label for='{$this->id}'>{$this->question}</label>" . PHP_EOL;
        
        if (MOBILE) {
            echo '<br />'; // put question and input on separate lines if on a mobile interface
        } else {
            echo "</td>\n    <td class='input'>" . PHP_EOL;
        }
        
        // display input cell
        
        echo $this->get_element();
        echo "    </td>", PHP_EOL;
        
        echo "</tr>" , PHP_EOL . PHP_EOL;
    }
}

class msgRow extends formElement {
    function print_formLine($editable=false) {
        $content = (!empty($this->custom_input)) ? $this->custom_input : $this->value;
    
        echo "<tr id='{$this->id}_row' title='{$this->tip}'>" . PHP_EOL;
        echo "    <td colspan='10' id='{$this->id}'>{$content}</td>", PHP_EOL;
        echo "</tr>" , PHP_EOL . PHP_EOL;
    }
}

class hiddenInput extends formElement {
    function print_formLine($editable=false) {
        echo "<input type='hidden'" . PHP_EOL;
        echo "    name='"     . $this->id     . "'" . PHP_EOL;
        echo "    id='"         . $this->id     . "'" . PHP_EOL;
        echo "    value='"     . $this->value     . "' />" . PHP_EOL;
    }
}

class ranking extends formElement {
    function print_formLine($editable=false) {
        echo "<tr class='ranking' title='", $this->tip, "' id='row_", str_replace('q', '', $this->id), "'>" . PHP_EOL;    
        
        // displays dragging handle
        echo "<td class='handle'></td>", PHP_EOL;
        
        // display question cell
        echo "<td>{$this->question}" . PHP_EOL;
        echo $this->get_element();
        echo "    </td>", PHP_EOL;
        
        echo "</tr>" , PHP_EOL . PHP_EOL;
    }

    function get_element() {
        $element_text  = "<input type='hidden'" . PHP_EOL;
        $element_text .= "    name='"     . $this->id     . "'" . PHP_EOL;
        $element_text .= "    id='"         . $this->id     . "'" . PHP_EOL;
        $element_text .= "    value='"     . $this->value     . "' />" . PHP_EOL;
        
        return $element_text;
    }
}

class datemenu extends formElement {
    /*public $startyear;                // date at top of year select
    public $endyear = 1900;            // date at end of year select
    public $show = array('year', 'month', 'day');    // which date elements to show and in what order
    
    function set_startyear($x) {
        switch ($x) {
            case 0:
                $x = date('Y');
                break;
            case 1:
                $birthyear = intval(substr($_SESSION['birthday'], 0 , 4));
                $x = ($birthyear>1900 && $birthyear<=date('Y')) ? $birthyear : 1900;
                break;
        }
        $this->startyear = $x; 
    }
    function get_startyear() { return $this->startyear; }
    function set_endyear($x) { 
        switch ($x) {
            case 0:
                $x = date('Y');
                break;
            case 1:
                $birthyear = intval(substr($_SESSION['birthday'], 0 , 4));
                if ($birthyear>1900 && $birthyear<=date('Y')) $x = $birthyear;
                break;
        }
        $this->endyear = $x; 
    }
    function get_endyear() { return $this->endyear; }
    function set_years($start, $end) { 
        $this->set_startyear($start);
        $this->set_endyear($end);
    }
    function set_show($x) { $this->show = $x; }
    function get_show() { return $this->show; }*/
    
    public $minDate = '-120y';
    public $maxDate = '+0y';
    
    function get_mindate() { return $this->minDate; }
    function set_mindate($x) { $this->minDate = $x; }
    function get_maxdate() { return $this->maxDate; }
    function set_maxdate($x) { $this->maxDate = $x; }
    function set_years($min, $max) { 
        $this->set_mindate($min);
        $this->set_maxdate($max);
    }
    
    function get_element() {            
        $default = (empty($this->value)) ? '' : $this->value['year'] . "-" . $this->value['month'] . "-" . $this->value['day'];
        
        preg_match('/[+-]\d+y/', $this->minDate, $minY);
        preg_match('/[+-]\d+y/', $this->maxDate, $maxY);
        $minYear = (count($minY) > 0) ? str_replace('y', '', $minY[0]) : '-1';
        $maxYear = (count($maxY) > 0) ? str_replace('y', '', $maxY[0]) : '+1';
        
        // display date menu
        $element_text = sprintf('<input type="text" class="datepicker" name="%s" id="%s" 
            value="%s" placeholder="%s" yearrange="%s:%s" mindate="%s" maxdate="%s" />',
            $this->id,
            $this->id,
            $default,
            "yyyy-mm-dd",
            $minYear,
            $maxYear,
            $this->minDate,
            $this->maxDate
        );
        
        return $element_text;
    }
}

class select extends formElement {
    public $options;
    public $value = 'NULL';
    public $className;
    public $null = true;
    
    function set_options($opts, $start=null, $end=null) { $this->options = $opts; }
    function get_options() { return $this->options; }
    function set_null($n) { $this->null = $n; }
    function get_null() { return $this->null; }
    function set_className($n) { $this->className = $n; }
    function get_className() { return $this->className; }
    
    function get_element() {
        $element_text = '';
        
        $element_text .=  "<select name='{$this->id}'" . PHP_EOL;
        if (!empty($this->className)) $element_text .=  "    class='{$this->className}'" . PHP_EOL;
        $element_text .=  "    id='{$this->id}'" . PHP_EOL;
        foreach ($this->eventHandlers as $eventHandler => $function) { 
            $element_text .=  "    $eventHandler=\"$function\"" . PHP_EOL; 
        }
        $element_text .=  ">" . PHP_EOL;
        
        if ($this->null) { 
            $sel = ($this->value == 'NULL') ? " selected='selected'" : "";
            $element_text .=  "    <option value='NULL'$sel></option>" . PHP_EOL;
        }
        
        foreach($this->options as $value1 => $display) {
            if (is_array($display)) {
                $element_text .=  "    <optgroup label='$value1'>" . PHP_EOL;
                foreach ($display as $value2 => $display2) {
                    settype($value2, "string");
                    $sel = ($this->value == $value2) ? " selected='selected'" : "";
                    $element_text .=  "    <option value='$value2'$sel>$display2</option>" . PHP_EOL;
                }
                $element_text .=  "    </optgroup>\n";
            } else {
                settype($value1, "string");
                $sel = ($this->value == $value1) ? " selected='selected'" : "";
                $element_text .=  "    <option value='$value1'$sel>$display</option>" . PHP_EOL;
            }
        }
        $element_text .=  "</select>" . PHP_EOL . PHP_EOL;
        
        return $element_text;
    }
}

class countries extends select{
    function __construct($id, $variable_name, $current_value='') {
        $this->id = $id;
        $this->set_name($variable_name);
        $this->set_value($current_value);

        // country-specific settings
        $cquery = new myQuery('SELECT id, country FROM countries ORDER BY IF(country="none", "ZZZZZ", country)');
        $countries = $cquery->get_assoc(false, 'id', 'country');
        $this->options = $countries;
        $this->className = 'countries';
    }
    
    function set_options($opts, $start=null, $end=null) {
        $cquery = new myQuery('SELECT id, country FROM countries ORDER BY IF(country="none", "ZZZZZ", country)');
        $countries = $cquery->get_assoc(false, 'id', 'country');
        if (empty($opts)) {
            $this->options = $countries;
        } else {
            $this->options = array($opts, $countries);
        }
    }
}

class selectnum extends select {
    public $startnum;
    public $endnum;

    function set_options($opts, $start=null, $end=null) {
        $this->startnum = $start;
        $this->endnum = $end;
    
        $numberoptions = array();
        
        $range = range($start, $end);
        foreach($range as $k=>$v) {
            $numberoptions[$v] = $v;
        }
        
        if (!is_array($opts)) { $opts = array(); }    
        
        //$this->options = array_merge($opts, $numberoptions);
        $this->options = $opts + $numberoptions;
        
        $this->set_className('selectnum');
    }
}

class radio extends select {
    public $orientation = 'horiz';
    
    function set_orientation($x) { $this->orientation = $x; }
    function get_orientation() { return $this->orientation; }

    function get_element() {
        $element_text = '';
        
        if ($this->orientation == "vertical" ) {
            $element_text .=  "<ul class='vertical_radio' id='{$this->id}'>" . PHP_EOL;
        } else {
            $element_text .=  "<ul class='radio' id='{$this->id}'>" . PHP_EOL;
        }
        
        foreach($this->options as $value => $display) {
            $element_text .=  '    <li>' . $this->create_radio($value, $display) . '</li>' . PHP_EOL;
        }
        
        $element_text .=  "</ul>" . PHP_EOL;
        
        return $element_text;
    }
    
    function create_radio($value, $display) {
        settype($value, "string");
        $sel = ($this->value === $value) ? " checked='checked'" : "";
        $r =  "<input type='radio' name='{$this->id}' value='$value' id='{$this->id}_$value'$sel />";
        $r .= "<label for='{$this->id}_$value'>$display</label>";
        
        return $r;
    }
}

class radiorow extends radio {
    function print_formLine($editable=false) {
        echo "<tr title='{$this->tip}' id='{$this->id}_row'>" . PHP_EOL;    
        
        if (MOBILE) {
            // put question and input on separate lines if on a mobile interface
            echo "<td class='question' colspan='20'><label for='{$this->id}'>{$this->question}</label></td></tr>" . PHP_EOL; 
            echo "<tr class='mobile_radiorow_div'><td colspan='20'></td></tr><tr><td class='input'>";
        } else {
            // display question cell
            echo "<td class='question'><label for='{$this->id}'>{$this->question}</label>" . PHP_EOL;
            echo "</td>\n    <td class='input'>" . PHP_EOL;
        }
        
        // display input cell
        
        echo $this->get_element();
        echo "    </td>", PHP_EOL;
        
        echo "</tr>" , PHP_EOL . PHP_EOL;
    }

    function get_element() {
        $radiobuttons = array();
        
        if    (is_array($this->options)) {
            foreach($this->options as $value => $display) {
                $radiobuttons[] = $this->create_radio($value, $display);
            }
            
            $element_text = implode('</td><td class="input">', $radiobuttons);
            
            return $element_text;
        } else {
            return 'The radiobutton array is not set: ' . $this->options;
        }
    }
}

class radiorev extends radiorow {
    function set_options($opts, $start=null, $end=null) { 
        // reverse order of options, preserving keys
        $this->options = array_reverse($opts, true); 
    }
}

class radioanchor extends radio {
    public $low_anchor;
    public $high_anchor;
    public $randomize = false;
    
    function set_options($opts, $start=null, $end=null) {
        $this->options = array_combine(range(1, $opts), range(1, $opts));
        $this->low_anchor = $start;
        $this->high_anchor = $end;
    }
    
    function set_randomize($x) { $this->randomize = $x; }
    function get_randomize() { return $this->randomize; }
    
    function get_element() {        
        $element_text .= "        <table class='radioanchor' id='{$this->id}'><tr>" . PHP_EOL;
        
        if ($this->randomize && rand(0,1)) {
            $element_text .= "            <td class='anchor'>{$this->high_anchor}</td>" . PHP_EOL;
            
            $rev_options = array_reverse($this->options, true);
            foreach($rev_options as $value => $display) {            
                $element_text .= '            <td>' . $this->create_radio($value, $display) . '</td>' . PHP_EOL;
            }
            $element_text .=   "            <td class='anchor'>{$this->low_anchor}</td>" . PHP_EOL;
        
        } else {
            $element_text .= "            <td class='anchor'>{$this->low_anchor}</td>" . PHP_EOL;
            
            foreach($this->options as $value => $display) {
                $element_text .= '            <td>' . $this->create_radio($value, $display) . '</td>' . PHP_EOL;
            }
            $element_text .=   "            <td class='anchor'>{$this->high_anchor}</td>" . PHP_EOL;
        }
        $element_text .=   "        </tr></table>\n";
        
        return $element_text;
    }
}

class input extends formElement {
    public $type = 'text';
    public $maxlength = 255;
    public $width = 300;
    public $autocomplete = 'off';
    public $int_only = false;
    public $placeholder = '';
    
    function set_type($t) {
        if (in_array($t, array('text', 'password', 'file', 'tel', 'search', 'url', 'email', 'datetime', 'date', 'month', 'week', 'time', 'datetime-local', 'number', 'range', 'color'))) {
            $this->type = $t;
        }
    }
    function get_type() { return $this->type; }
    
    function set_maxlength($t) {
        if ($t < 255 && $t>0) {
            $this->maxlength = $t;
        }
    }
    function get_maxlength() { return $this->maxlength; }
    
    function set_width($t) {
        if ($t > 10) { 
            $this->width = $t;
        }
    }
    function get_width() { return $this->width; }
    
    function set_autocomplete($t) {
        if (in_array($t, array('on', 'off'))) {
            $this->autocomplete = $t;
        }
    }
    function get_autocomplete() { return $this->autocomplete; }
    
    function set_placeholder($t) { $this->placeholder = $t; }
    function get_placeholder() { return $this->placeholder; }
    
    function set_int_only($x) { $this->int_only = $x; }
    function get_int_only() { return $this->int_only; }
    
    function get_element() {
        $element_text = '';

        $element_text .=   "<input name='"         . $this->id             . "'" . PHP_EOL;
        $element_text .=   "    class='textinput'" . PHP_EOL;
        $element_text .=   "    id='"             . $this->id             . "'" . PHP_EOL;
        $element_text .=   "    type='"         . $this->type             . "'" . PHP_EOL;
        $element_text .=   "    value='"         . $this->value             . "'" . PHP_EOL;
        $element_text .=   "    maxlength='"     . $this->maxlength         . "'" . PHP_EOL;
        $element_text .=   "    autocomplete='" . $this->autocomplete     . "'" . PHP_EOL;
        $element_text .=   "    placeholder='"     . $this->placeholder     . "'" . PHP_EOL;
        $element_text .=   "    style='width:"     . $this->width             . "px'" . PHP_EOL;
        if ($this->required) {
            $element_text .= "    required" . PHP_EOL;
        }
        if ($this->int_only) {
            $element_text .=   "    onkeyup='formatInt(this);'" . PHP_EOL;
        }
        foreach ($this->eventHandlers as $eventHandler => $function) { 
            $element_text .=   "    $eventHandler=\"$function\"" . PHP_EOL; 
        }
        $element_text .=   " />" . PHP_EOL . PHP_EOL;

        return $element_text;
    }
}

class textarea extends formElement {
    public $width = 300;
    public $height = 30;
    public $expandable = false;
    public $minheight = 30;
    public $maxheight = 300;
    public $textlimit = 0;
    
    function set_width($w) { 
        if (is_numeric($w)) { 
            $this->width = $w . 'px';
        } else {
            $this->width = $w;
        } 
    }
    function get_width() { return $this->width; }
    function set_height($h) { 
        if (is_numeric($h)) { 
            $this->height = $h . 'px';
        } else {
            $this->height = $h;
        } 
    }
    function get_height() { return $this->height; }
    function set_expandable($e) { $this->expandable = $e; }
    function get_expandable() { return $this->expandable; }
    function set_minheight($w) { $this->minheight = $w; }
    function get_minheight() { return $this->minheight; }
    function set_maxheight($h) { $this->maxheight = $h; }
    function get_maxheight() { return $this->maxheight; }
    function set_textlimit($t) { $this->textlimit = $t; }
    function get_textlimit() { return $this->textlimit; }
    
    function set_dimensions($w, $h, $e=false, $min=30, $max=300, $t=0) {
        $this->set_width($w);
        $this->set_height($h);
        $this->set_expandable($e);
        $this->set_minheight($min);
        $this->set_maxheight($max);
        $this->set_textlimit($t);
    }
    
    function get_element() {
        $element_text = '';
        
        $element_text .=   "<textarea name='{$this->name}'" . PHP_EOL;
        $element_text .=   "    id='{$this->name}'" . PHP_EOL;
        $element_text .=   "    style='width:{$this->width};height:{$this->height};'" . PHP_EOL;
        
        foreach ($this->eventHandlers as $eventHandler => $function) { 
            $element_text .=   "    $eventHandler=\"$function\"" . PHP_EOL;
        }
        
        if ($this->expandable == true) {
            $element_text .=   "    onfocus='textarea_expand(this, {$this->minheight}, {$this->maxheight})'" . PHP_EOL;
            $element_text .=   "    onblur='this.style.height=\"{$this->minheight}px\"'" . PHP_EOL;
        }
        
        if ($this->textlimit > 0) {
            $element_text .=   "    oninput='textlimit(this, {$this->textlimit})'" . PHP_EOL;
        }
        
        $element_text .=   ">{$this->value}</textarea>" . PHP_EOL . PHP_EOL;
        
        return $element_text;
    }
}

 
?>