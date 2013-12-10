<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax Playa
 *
 * This file must be in your /system/third_party/tax_playa directory of your ExpressionEngine installation
 *
 * @package     Tax Playa      
 * @author      Mark Croxton                        
 * @copyright   Copyright (c) 2013 Mark Croxton     
 * @link        http://hallmark-design.co.uk    
 */

// include dependencies
require_once PATH_THIRD.'taxonomy/models/taxonomy_model.php';

// Zenbu support: try to load Zenbu's playa fieldtype extension, if it exists.
// This will give us nicely formatted links in the Zenbu table, just like a native Playa ft
if (class_exists('Zenbu_playa_ft')) 
{
    class Dynamic_playa_ft extends Zenbu_playa_ft {}
} 
else 
{
    if ( ! class_exists('Playa_ft') )
    {
        require_once PATH_THIRD.'playa/ft.playa.php';
    }
    class Dynamic_playa_ft extends Playa_ft {}
}

class Tax_playa_ft extends Dynamic_playa_ft {

    public $tax, $EE;

    public $info = array(
        'name'      => 'Tax Playa',
        'version'   => '1.0.1'
    );

    protected static $init = TRUE;
    
    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    
        $this->tax = new Taxonomy_model();
        $this->site_id = $this->EE->config->item('site_id');

        $this->playa = "tax_playa";
    }

    /**
     * Fallback settings values
     * 
     * @param array
     * @return array 
     * @access private
     */ 
    private function _default_settings($data)
    {
        return array_merge(array(
            "tree_id"       => array(),
            "style"         => 'select',
            "depth"         => 0
        ), $data);
    }

    /**
     * Display the settings form for each custom field
     * 
     * @param $data mixed The field settings
     * @return string Override the field custom settings with custom html
     * @access public
     */
    public function display_settings($data)
    {
       $rows = $this->_field_settings($data);

        foreach ($rows as $row)
        {
            $this->EE->table->add_row($row[0], $row[1]);
        }
    }

    /**
     * Display cell setting
     * 
     * @param mixed
     * @return string
     * @access public
     */
    public function display_cell_settings($data)
    {
        return $this->_field_settings($data, TRUE);
    }

    /**
     * Display variable settings
     * 
     * @param mixed
     * @return string
     * @access public
     */
    public function display_var_settings($data)
    {
        return $this->_field_settings($data);
    }

    /**
     * Display field settings
     * 
     * @param mixed
     * @return string
     * @access private
     */
    private function _field_settings($data)
    {
        $data = $this->_default_settings($data);

        $row = array();
        
        $row[] = array(
            'Tree',
            $this->_tree_select($data['tree_id'], 'tax_playa_field_settings[tree_id]')
        );
        
        $row[] = array(
            'Style',
            $this->_build_multi_radios($data['style'], 'tax_playa_field_settings[style]')
        );  
        
        $depth_options = array(0,1,2,3,4,5,6,7,8,9,10);
        $row[] = array(
            'Depth',
            form_dropdown('tax_playa_field_settings[depth]', $depth_options, $data['depth'])
        ); 

        return $row; 
    }

    /**
     * Standard field - save settings
     * 
     * @return array
     * @access public
     */
    public function save_settings()
    {
        // remove empty values
        return $this->_sanitize_settings( $this->EE->input->post('tax_playa_field_settings') );
    }

    /**
     * Matrix - save settings
     * 
     * @return array
     * @access public
     */
    public function save_cell_settings($settings)
    {
        return $this->_sanitize_settings($settings['tax_playa_field_settings']);
    }

    /**
     * Low variables - save settings
     * 
     * @return array
     * @access public
     */
    public function save_var_settings()
    {
        return $this->_sanitize_settings($this->EE->input->post('tax_playa_field_settings'));
    }

    /**
     * Sanitize settings data
     * 
     * @param array
     * @return array
     * @access private
     */
    private function _sanitize_settings($settings)
    {
        return array_filter($settings);
    }
    
    /**
     * Display global settings
     * 
     * @return string
     * @access public
     */
    public function display_global_settings()
    {
        $this->EE->cp->add_to_head('
            <script type="text/javascript">
            $(document).ready(function() {
                $(\'.pageContents input\').attr("value", "OK");
            });             
            </script>');
            
        return '<p>This fieldtype requires valid licenses for <a href="http://devot-ee.com/add-ons/taxonomy">Taxonomy</a> and <a href="http://devot-ee.com/add-ons/playa">Playa</a>.</p>';     
    }
    
    /**
     * Publish form validation
     * 
     * @param array $data Contains the submitted field data.
     * @return mixed TRUE or an error message
     * @access public
     */
    public function validate($data)
    {
        // is this a required field?
        if ($this->settings['field_required'] == 'y')
        {
            // make sure there are selections
            if ( ! isset($data['selections']) || is_array($data['selections']) && ! array_filter($data['selections']))
            {
                return lang('required');
            }
        }

        return TRUE;
    }

    /**
     * Save normal field data
     *
     * @param array $data The selected entry ids
     * @return string Concatenated string
     * @access public
     */
    public function save($data)
    {
        $selections['selections'] = is_array($data)? $data : array($data);
        return parent::save($selections);
    }

    /**
     * Save matrix cell data
     *
     * @param array $data The selected entry ids
     * @return string Concatenated string
     * @access public
     */
    public function save_cell($data)
    {
        $selections['selections'] = is_array($data)? $data : array($data);
        return parent::save_cell($selections);
    }

    /**
     * Save variable data
     *
     * @param array $data The selected entry ids
     * @return string Concatenated string
     * @access public
     */
    public function save_var_field($data)
    {
        $selections['selections'] = is_array($data)? $data : array($data);
        return parent::save_var_field($selections);
    }
        
    /**
     * Display normal fieldtype
     *
     * @param string $data
     * @return string
     * @access public
     */
    public function display_field($data)
    {   
        switch ($this->settings['style'])
        {
            case "checkboxes" :
                $selected = $this->_get_selected_entry_ids($data);
                return $this->_node_checkboxes($selected, $this->field_name, $this->field_id, $this->settings);
            break;
            
            case "select" : default :
                $selected = $this->_get_selected_entry_ids($data);
                return $this->_node_select($selected, $this->field_name, $this->field_id, $this->settings);
            break;
        }
    }

    /**
     * Display matrix cell field
     *
     * @param string $data
     * @return string
     * @access public
     */
    public function display_cell($data)
    {   
        switch ($this->settings['style'])
        {
            case "checkboxes" :
                // have we included the Matrix script?
                if ( ! isset($this->cache['included_cell_resources']))
                {
                    $this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url('javascript/matrix.js').'"></script>');
                    $this->cache['included_cell_resources'] = TRUE;
                }
                $selected = $this->_get_selected_entry_ids($data);
                return $this->_node_checkboxes($selected, $this->cell_name, $this->field_id, $this->settings);
            break;
            
            case "select" : default :
                $selected = $this->_get_selected_entry_ids($data);
                return $this->_node_select($selected, $this->cell_name, $this->field_id, $this->settings);
            break;
        }
    }

    /**
     * Display variable field
     * 
     * @param string $data
     * @return string
     * @access public
     */
    public function display_var_field($data)
    {
        if (! $this->var_id) return;
        return $this->display_field($data);
    }

    // --------------------------------------------------------------------

    /**
     * Get selected entry IDs
     *
     * @param string $data
     * @return array
     * @access private
     */
    private function _get_selected_entry_ids($data)
    {
        $selected = array();

        // autosave data?
        if ( is_array($data) )
        {
            $selected = $data;
        }
        else if ( isset($_POST[$this->field_name]) && $_POST[$this->field_name] )
        {
            $selected = $_POST[$this->field_name];
        }
        else
        {
            // existing entry?
            $entry_id = $this->EE->input->get('entry_id');

            if ( ($this->var_id || $entry_id) && ( ! isset($this->cell_name) || isset($this->row_id) ) )
            {
                if ($this->var_id)
                {
                    $where = array(
                        'parent_var_id' => $this->var_id
                    );
                }
                else
                {
                    $where = array(
                        'parent_entry_id' => $entry_id,
                        'parent_field_id' => $this->field_id
                    );
                }

                // Matrix?
                if ( isset($this->cell_name) )
                {
                    $where['parent_col_id'] = $this->col_id;
                    $where['parent_row_id'] = $this->row_id;
                }

                $rels = $this->EE->db->select('child_entry_id')
                                     ->where($where)
                                     ->order_by('rel_order')
                                     ->get('playa_relationships');

                foreach ($rels->result() as $rel)
                {
                    $selected[] = $rel->child_entry_id;
                }
            }
        }
        
        return $selected;
    }   

    /**
     * Builds a string of radio buttons
     *
     * @return string
     * @access private
     */
    private function _build_multi_radios($data, $name)
    {
        return form_radio($name, 'select', ($data == 'select') ) . NL
            . 'Select menu' . NBS.NBS.NBS.NBS.NBS . NL
            . form_radio($name, 'checkboxes', ($data == 'checkboxes') ) . NL
            . 'Checkboxes';
    }
    
    /**
     * Taxonomy tree select menu
     *
     * @return string select HTML
     * @access private
     */
    private function _node_select($data, $name, $field_id=false, $settings=array(), $multiselect=false, $default='none', $attr='')
    {
        $settings = $this->_default_settings($settings);
        $taxonomy_data = $this->_get_taxonomy($settings);
        $options = array();
        
        foreach($taxonomy_data as $tree)
        {
            foreach ($tree as $node)
            {   
                $options[] = array(
                     'id'    => $node['entry_id'],
                     'title' => str_repeat('--', $node['depth']) . $node['label']
                );
            }
        }

        if ( ! is_array( $data ))
        {
            $data = array($data);
        }
        
        return $this->_field_settings_select($name, $options, $data, $multiselect, FALSE, $default, $attr);
    }
    
    /**
     * Taxonomy tree checkboxes
     *
     * @return string HTML
     * @access private
     */
    private function _node_checkboxes($data, $name, $field_id = false, $settings=array(), $multiselect = false)
    {
        $settings = $this->_default_settings($settings);
        $taxonomy_data = $this->_get_taxonomy($settings);
        
        // insert assets
        if (self::$init)
        {   
            $theme_url = $this->_theme_url();

            $this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$theme_url.'css/jquery.checkboxtree.css">');
            $this->EE->cp->add_to_head('<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>');
            $this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$theme_url.'javascript/jquery.checkboxtree.js"></script>');
            
            $this->EE->cp->add_to_foot('
            <script type="text/javascript">
            $(document).ready(function() {

                $(\'.root\').addClass(\'collapsed\'); // stop widget closing root branches

                $(\'.checkboxTree > ul\').each(function() {

                    var $self = $(this);

                    $self.checkboxTree({  
                        initializeChecked: \'expanded\',
                        initializeUnchecked: \'collapsed\',
                        checkChildren: false,
                        checkParents: false,
                        onCheck: {
                            ancestors: \'\', 
                            descendants: \'\', 
                            node: \'\'
                        },
                        onUncheck: {
                            ancestors: \'\',
                            descendants: \'\',
                            node: \'\'
                        }   
                    });

                    // expand root branches
                    $self.data(\'checkboxTree\').expand($self.find(\'.root\'));
                   
                    // show li after tree has expanded
                    setTimeout(function(){
                        $self.find(\'li\').css(\'visibility\', \'visible\');
                    }, 500);
                });
            })
            </script>');
    
            self::$init = false;                    
        }

        $ul_open = false;
        $last_node_depth = 0;
        $last = "";
        $html = "<ul>";

        foreach($taxonomy_data as $tree)
        {
            // Begin building the tree
            foreach ($tree as $node)
            {
                if ($node['depth'] > $last_node_depth)
                {
                    $html = substr($html, 0, -6);
                    $html .= "\n<ul>\n";
                    $ul_open = true;
                }

                // Close a sub nav
                if ($node['depth'] < $last_node_depth)
                {
                    // Calculate how many levels back we need to go
                    $back_to = $last_node_depth - $node['depth'];
                    $html .= str_repeat("</ul>\n</li>\n", $back_to);
                    $ul_open = false;
                }

                $node_title = htmlspecialchars($node['label']);

                $li_attr='';
                if ($node['depth'] == 0 && count($taxonomy_data) > 1)
                {   
                    // if we have more than one taxonomy tree, we want to expand the root branches by default
                    $li_attr = ' class="root"';
                }
                
                if ($node['entry_id'] > 0)
                {
                    $checked = in_array($node['entry_id'], $data) ? TRUE : FALSE;
                    $list_item = '<li'.$li_attr.'>'.form_checkbox($name.'[]', $node['entry_id'], $checked).NBS.$node_title.'</li>'."\n";
                }
                else
                {
                    $list_item = '<li'.$li_attr.'>'.NBS.$node_title.'</li>'."\n";
                }
                
                $html .= $list_item;

                $last_node_depth = $node['depth'];
            }
        }
        
        $html .= "</ul>";
        $html = '<div class="checkboxTree">'.$html.'</div>';
        
        return $html;
    }

    /**
     * Get taxonomy tree
     *
     * @param array 
     * @return array
     * @access private
     */
    private function _get_taxonomy($settings = array())
    {
        $settings = $this->_default_settings($settings);
        $taxonomy_data = array();
        $tree_count = count($settings['tree_id']);

        foreach($settings['tree_id'] as $tree_id)
        {
            $this->tax->set_table($tree_id);
            $taxonomy = array_values($this->tax->get_flat_tree());
            $data = array();

            // apply depth
            foreach($taxonomy as $node)
            {
                // if we only have one tree, remove the root node
                if ($tree_count === 1 && $node['depth'] > 0 || $tree_count > 1)
                {
                    // apply max depth if not zero
                    if ($node['depth'] <= $settings['depth'] || $settings['depth'] === 0)
                    {
                        // offset depth if there's only one tree
                        if ($tree_count === 1)
                        {
                            $node['depth'] = $node['depth'] - 1;
                        }

                        $data[] = $node;
                    }
                }
            }

            $taxonomy_data[] = $data;
        }

        return $taxonomy_data;
    }
    
    /**
     * Tree select
     *
     * @return string select HTML
     * @access private
     */
    private function _tree_select($data, $name)
    {
        $trees  = $this->tax->get_trees();
        $options    = array();

        foreach($trees as $row) 
        {
            $options[] = array(
                    'id'    => $row['id'],
                    'title' => $row['label']
            );      
        }
        return $this->_field_settings_select($name, $options, $data, TRUE, FALSE);
    }

    /**
     * Field settings select menu
     *
     * @return string
     * @access private
     */
    private function _field_settings_select($name, $rows, $selected_ids, $multi = TRUE, $optgroups = TRUE, $default = NULL, $attr='')
    {
        $attr = ' style="width: 230px" '.$attr;
        
        $options = $this->_field_settings_select_options($rows, $selected_ids, $optgroups, $row_count, $default);
        
        if ($multi)
        {
            return '<select name="'.$name.'[]" multiple="multiple" size="'.($row_count < 10 ? $row_count : 10).'"'.$attr.'>'
               . $options
               . '</select>';
        }
        else
        {
            return '<select name="'.$name.'"'.$attr.'>'
               . $options
               . '</select>';
        }
    }

    /**
     * Field settings select menu options
     *
     * @return string
     * @access private
     */
    private function _field_settings_select_options($rows, $selected_ids = array(), $optgroups = TRUE, &$row_count = 0, $default = NULL)
    {
        if ($optgroups) $optgroup = '';
        $options = '';

        if (NULL !== $default)
        {
            $options = '<option value=""'.($selected_ids || empty($data) ? '' : ' selected="selected"').'>&mdash; '.lang($default).' &mdash;</option>';
        }
        $row_count = 1;

        foreach ($rows as $row)
        {
            if ($optgroups && isset($row['group']) && $row['group'] != $optgroup)
            {
                if ($optgroup) $options .= '</optgroup>';
                $options .= '<optgroup label="'.$row['group'].'">';
                $optgroup = $row['group'];
                $row_count++;
            }

            $selected = in_array($row['id'], $selected_ids) ? 1 : 0;
            $options .= '<option value="'.$row['id'].'"'.($selected ? ' selected="selected"' : '').'>'.$row['title'].'</option>';
            $row_count++;
        }

        if ($optgroups && $optgroup) $options .= '</optgroup>';

        return $options;
    }

    /**
     * Get the theme url
     *
     * @return string
     * @access private
     */
    private function _theme_url($uri = '')
    {
        $theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url').'third_party/';
        $theme_url = $theme_folder_url.'tax_playa/';
        return $theme_url . $uri;
    }
}

// END tax_playa_ft class

/* End of file ft.tax_playa.php */
/* Location: ./system/expressionengine/third_party/tax_playa/ft.tax_playa.php */