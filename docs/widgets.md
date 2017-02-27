# Core Plus Widgets

Widgets in Core Plus allow the site admin to inject functionality into their web applications
that pull from your custom component.  They are generally added from /widget/admin into 
either specific pages or the entire site.

## Component XML

    <widgets>
		<widget baseurl="/classname/widgetmethod" title="Something Meaningful to the Admin"/>
	</widgets>

## Component PHP Code

    class ClassNameWidget extends Widget_2_1 {
        // Set this to true to make it a "simple" widget.
	    public $is_simple = true;

        // Contains an array of settings and the default values thereof.
        // These are used 
        public $settings = [
		    'title' => 'Content Title',
            'content' => '',
        ];

	public function getFormSettings(){

		$settings = [
			[
				'type'        => 'text',
				'name'        => 'title',
				'title'       => 'Displayed Title',
				'description' => 'Displayed title on the page where this widget is added to.',
			],
		    [
			    'type' => 'wysiwyg',
		        'name' => 'content',
		        'title' => 'Widget Content',
		    ]
		];

		return $settings;
	}

	/**
	 * Get the path for the preview image for this widget.
	 *
	 * Should be an image of size 210x70, 210x140, or 210x210.
	 *
	 * @return string
	 */
	public function getPreviewImage(){
		// Extend this method in your class and return the path you need.
		// Optional.
		return 'assets/images/previews/templates/widgets/content/custom-content-area.png';
	}

	/**
	 * Widget to display a simple site search box
	 */
	public function execute(){
		$view = $this->getView();

		$view->assign('title', $this->getSetting('title'));
		$view->assign('content', \Core\parse_html($this->getSetting('content')));
	}
}