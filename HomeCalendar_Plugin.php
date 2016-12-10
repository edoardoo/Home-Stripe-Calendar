<?php


include_once('HomeCalendar_LifeCycle.php');

class HomeCalendar_Plugin extends HomeCalendar_LifeCycle {

    /**
    * See: http://plugin.michael-simpson.com/?page_id=31
    * @return array of option meta data.
    */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            'AmAwesome' => array(__('I like this awesome plugin', 'my-awesome-plugin'), 'false', 'true'),
            'CanDoSomething' => array(__('Which user role can do something', 'my-awesome-plugin'),
            'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

    //    protected function getOptionValueI18nString($optionValue) {
    //        $i18nValue = parent::getOptionValueI18nString($optionValue);
    //        return $i18nValue;
    //    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'Home Calendar';
    }

    protected function getMainPluginFileName() {
        return 'home-calendar.php';
    }

    /**
    * See: http://plugin.michael-simpson.com/?page_id=101
    * Called by install() to create any database tables if needed.
    * Best Practice:
    * (1) Prefix all table names with $wpdb->prefix
    * (2) make table names lower case only
    * @return void
    */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
    * See: http://plugin.michael-simpson.com/?page_id=101
    * Drop plugin-created tables on uninstall.
    * @return void
    */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
    * Perform actions when upgrading from version X to version Y
    * See: http://plugin.michael-simpson.com/?page_id=35
    * @return void
    */
    public function upgrade() {
    }

    public function addActionsAndFilters() {

        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }
        add_shortcode( 'cal_widget', array( 'HomeCalendar_Plugin', 'calWidget_shortcode' ) );
        add_action( 'widgets_init', array( 'HomeCalendar_Plugin', 'home_calendar_widgets_init' ) );

        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37


        // Adding scripts & styles to all pages
        // Examples:


        if( !is_admin() ){
            wp_register_script('jquery1', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://code.jquery.com/jquery-1.12.4.js", null, true);
            wp_enqueue_script('jquery1');
        }



        wp_enqueue_style('style', plugins_url('/css/style.css', __FILE__));
        wp_enqueue_script('calendarSlider', plugins_url('/js/calendarSlider.js', __FILE__));


        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

    }

    public static function calWidget_shortcode( $atts ) {
        $calendar = $atts['calendar'];
        $events = get_spider_event($calendar);
        return print_events($events);
    }

    public static function home_calendar_widgets_init() {

        register_sidebar( array(
            'name'          => 'Home Calendar',
            'id'            => 'home_calendar',
            'before_widget' => '<div>',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="rounded">',
            'after_title'   => '</h2>',
        ) );

    }

}


function get_spider_event($calendar_id) {
    //pretty similar to spiderCalendar function
    global $wpdb;
    $order = " ORDER BY title ASC";
    $sort["default_style"] = "manage-column column-autor sortable desc";
    $sort["sortid_by"] = "title";
    $sort["custom_style"] = "manage-column column-title sorted asc";
    $sort["1_or_2"] = "2";
    if (isset($_POST['page_number'])) {
        if (isset($_POST['order_by']) && esc_html($_POST['order_by']) != '') {
            $sort["sortid_by"] =esc_sql(esc_html(stripslashes($_POST['order_by'])));
        }
        if (isset($_POST['asc_or_desc']) && (esc_html($_POST['asc_or_desc']) == 1)) {
            $sort["custom_style"] = "manage-column column-title sorted asc";
            $sort["1_or_2"] = "2";
            $order = "ORDER BY " . $sort["sortid_by"] . " ASC";
        }
        else {
            $sort["custom_style"] = "manage-column column-title sorted desc";
            $sort["1_or_2"] = "1";
            $order = "ORDER BY " . $sort["sortid_by"] . " DESC";
        }
        if (isset($_POST['page_number']) && (esc_html($_POST['page_number']))) {
            $limit = (esc_sql(esc_html(stripslashes($_POST['page_number']))) - 1) * 20;
        }
        else {
            $limit = 0;
        }
    }
    else {
        $limit = 0;
    }
    if (isset($_POST['search_events_by_title'])) {
        $search_tag = esc_sql(esc_html(stripslashes($_POST['search_events_by_title'])));
    }
    else {
        $search_tag = "";
    }
    if ($search_tag) {
        $where = ' AND ' . $wpdb->prefix . 'spidercalendar_event.title LIKE "%%' . like_escape($search_tag) . '%%"';
    }
    else {
        $where = '';
    }


    $endDate = date('Y-m-d', strtotime('+2 months'));
    $nowDate = date('Y-m-d');

    if (isset($_POST['startdate']) && esc_html($_POST['startdate'])) {
        $where .= ' AND ' . $wpdb->prefix . 'spidercalendar_event.date > \'' . esc_sql(esc_html(stripslashes($_POST['startdate']))) . '\' ';
    }

    $where .= ' AND ' . $wpdb->prefix . 'spidercalendar_event.date < \'' .esc_sql(esc_html(stripslashes( $endDate ))). '\' ';
    $where .= ' AND ' . $wpdb->prefix . 'spidercalendar_event.date >= \'' .esc_sql(esc_html(stripslashes( $nowDate ))). '\' ';

    // Get the total number of records.
    $query = $wpdb->prepare ("SELECT COUNT(*) FROM " . $wpdb->prefix . "spidercalendar_event WHERE calendar=%d " . $where . " ", $calendar_id);
    
    $total = $wpdb->get_var($query);
    $pageNav['total'] = $total;
    $pageNav['limit'] = $limit / 20 + 1;

    $queryEvents = $wpdb->prepare ("SELECT " . $wpdb->prefix . "spidercalendar_event.*, " . $wpdb->prefix . "spidercalendar_event_category.title as cattitle FROM " . $wpdb->prefix . "spidercalendar_event LEFT JOIN " . $wpdb->prefix . "spidercalendar_event_category ON " . $wpdb->prefix . "spidercalendar_event.category=" . $wpdb->prefix . "spidercalendar_event_category.id
    WHERE calendar=1 " . $where . " " . $order . " "  );
    $rows = $wpdb->get_results($queryEvents);
    
    return $rows;

}

function print_events( $events ){
    //REMEMBER TO CHANGE THE URL

    $printedLayout = '<div class="calendarWrapper"><i class="calendarArrow icon-to-left-arrow back"></i><div class="hContainer"><ul id="calendarPlugin" class="box_transition">';

    $groupedEvents = array();

    foreach($events as $key => $item)
    {
        $tmpElement = json_decode(json_encode($item),true);
        $groupedEvents[$tmpElement['date']][$key] = $item;
    }

    ksort($groupedEvents);


    foreach ($groupedEvents as $keyDay => $day) {


        $printedLayout .= '<li class="calendarEvent">
        <div class= "row container data">
        <div class="col col40">
            <div class="row day">'.date("D", strtotime($keyDay)).'</div>
            <div class="row month">'.date("M", strtotime($keyDay)).'</div>
        </div>
        <div class="col col50 dayNumber">'.date("d", strtotime($keyDay)).'</div>
        </div>
        <div class= "row">
        <ul>';
        foreach ($day as $keyEvent => $event) {
            $tmpEvent = json_decode(json_encode($event),true);
            //CHANGE THE URL HERE:
            $tmpLink = 'http://example.com/wp-admin/admin-ajax.php?action=spidercalendarbig&theme_id=13&calendar_id=1&ev_ids='.$tmpEvent['id'].'&eventID='.$tmpEvent['id'].'&date='.$tmpEvent['date'].'&many_sp_calendar=1&cur_page_url=http://example.com/';
                                  

            $printedLayout .= ' <li class="eventDetail">
                                    <a href="#" onclick="loadEventDetails('.$tmpEvent['id'].', \''.$tmpEvent['date'].'\'); return false;" >'.$tmpEvent['title'].'</a>


                                </li>';

        }

        $printedLayout .= '     </ul>
        </div>
        </li>';
    }
    $printedLayout .= '</ul></div><i class="calendarArrow icon-to-right-arrow next"></i></div><span class="closeDetails hidden" onclick="closeEventDetails()">Close</span><div id="showEventDetails"></div>';

    return $printedLayout;
}
