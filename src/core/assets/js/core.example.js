/**
 * Created with JetBrains PhpStorm.
 * User: powellc
 * Date: 5/10/12
 * Time: 9:33 PM
 * This file is simply to provide editors a basis to know how the Core object is setup and what is available from it
 * out of the box.
 *
 * This will NOT be included in any page, as it is rendered from within the Page system instead.
 */

var Core = {
    /**
     * Version of Core currently installed
     * @exampleText 1.9.0
     * @type String
     */
    Version: "1.9.0",
    /**
     * Web directory of the site
     *
     * This is the full web path used to access the site.
     * If installed in the web root, it is simply "/"
     */
    ROOT_WDIR: "/~powellc/coreplus/",
    /**
     * Fully resolved URL of the site root
     */
    ROOT_URL: "http://theta.lan/~powellc/coreplus/",
    /**
     * Fully resolved SECURE URL of the site root
     */
    ROOT_URL_SSL: "http://theta.lan/~powellc/coreplus/",
    /**
     * Fully resolved NONSECURE URL of the site root
     */
    ROOT_URL_NOSSL: "http://theta.lan/~powellc/coreplus/"
};