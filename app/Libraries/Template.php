<?php
namespace App\Libraries;

/**
 * Name:    Template
 *
 * Created: 06.03.2020
 *
 * Description:
 *
 * Original Author: Vincent Kilchherr
 *
 */
class Template
{

    /**
     * Contents page
     *
     * @var array
     */
    protected $site_data = [];

    protected $header_filepath = 'templates/header.php';
    protected $footer_filepath = 'templates/footer.php';


    /**
     * Set contents
     *
     * @param string $name  Variable name in the template
     * @param string $value Value to be passed to variable
     * @return void
     */
    public function set($name_or_array, $data=false): void
    {
    	if(is_array($name_or_array)) {
    		foreach($name_or_array as $key=>$val) {
    			$this->set($key, $val);
    		}
    	} else {
	        // Set name data
	        $this->site_data[$name_or_array] = $data;
    	}
    }

    public function getSiteData($key = false)
    {
        if ($key):
            return $this->site_data[$key];
        else:
            return $this->site_data;
        endif;
    }

    public function setHeader($header_filepath='') {
      $this->header_filepath = $header_filepath;
    }

    public function setFooter($footer_filepath='') {
      $this->footer_filepath = $footer_filepath;
    }
	
	public function setHeaderFooter($header_filepath='', $footer_filepath='') {
		$this->setHeader($header_filepath);
		$this->setFooter($footer_filepath);
	}

    /**
     * Loading template
     *
     * @param string $view      Path of view to be used
     * @param array  $options   Options supported by the view function <https://codeigniter4.github.io/userguide/general/common_functions.html?highlight=view#view>
     * @return string
     */
    public function load(string $view = '', array $options = []): void
    {
        // Set variable $contents in template
        // Return template + view
        echo view($this->header_filepath, $this->site_data);
        echo view($view, $this->site_data, $options);
        echo view($this->footer_filepath, $this->site_data);
    }

    public function return(string $view = '', array $options = []): string
    {
        // Set variable $contents in template
        $return = '';

        // Return template + view
        $return .= view($this->header_filepath, $this->site_data);
        $return .= view($view, $this->site_data, $options);
        $return .= view($this->footer_filepath, $this->site_data);

        return $return;
    }

    public function single(string $view = '', array $site_data = []): string
    {
        // Set variable $contents in template
        $return = '';

        foreach($site_data as $site_key=>$site_value):
          $this->site_data[$site_key] = $site_value;
        endforeach;

        // Return template + view
        $return .= view($view, $this->site_data);

        return $return;
    }

}
