<?php

class PageWidget extends Widget_2_1 {
	/**
	 * This is a widget to display siblings on a given page.
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function siblingsnavigation() {
		return 'Please use /navigation/siblings instead.';
	}

	/**
	 * This is a widget to display siblings AND the active page's children on a given page.
	 *
	 * The page is dynamic based on the currently viewed page.
	 *
	 * @return int
	 */
	public function siblingsandchildrennavigation() {
		return 'Please use /navigation/siblingsandchildren instead.';
	}
}