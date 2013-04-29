/**
 * Simple script to "fancify" any IP address on the page.
 *
 * Generally used on admin pages where the IP can be blocked ;)
 */

$(function(){
	// List of IPs currently found.  Keep for cache.
	var ips = {};

	function render_fancy_ip($node, data){
		// Data needs to be an associative array of:
		// country, network, ip, flag_sm, and flag_lg.
		var currenthtml = $node.html();
		$node.attr('ip', data.ip).html('<img src="' + data.flag_sm + '" title="' + data.country_name + '" alt="' + data.country + '"/> ' + currenthtml);
		$node.append(ips[data.query].tooltip);
	};

	function build_fancy_overlay(data){
		// Data needs to be an associative array of:
		// country, network, ip, flag_sm, and flag_lg.

		var html =
			// Begin the tooltip wrapper
			'<div class="ip-tooltip" style="display:none;">' +
			'<span class="nowrap"><span class="ip-tooltip-label">Organization:</span>' + data.organization + '</span><br/>' +
			'<span class="ip-tooltip-label">Country:</span>' + data.country_name + '<br/>' +
			'<span class="ip-tooltip-label">IP:</span>' + data.ip +
			' <a class="ip-tooltip-ban-link" href="' + Core.ROOT_URL + 'security/blacklistip/add?ip_addr=' + data.ip + '/32" title="Ban IP">' +
			'<i class="icon-thumbs-down"></i>' +
			'</a><br/>' +
			'<span class="ip-tooltip-label">Network:</span>' + data.network +
			' <a class="ip-tooltip-ban-link" href="' + Core.ROOT_URL + 'security/blacklistip/add?ip_addr=' + data.network + '" title="Ban Network">' +
			'<i class="icon-thumbs-down"></i>' +
			'</a><br/>' +
			// end the tooltip wrapper
			'</div>',
			$html;

		//$html = $(html);
		//$('body').append($html);

		ips[data.query]['tooltip'] = html;
	};

	$('.ip')
		.each(function(){
			var ip = Core.Strings.trim($(this).text());
			if(typeof ips[ip] != 'undefined'){
				// It is either in the process of being looked up, or already has been.
				if(ips[ip].result){
					render_fancy_ip($(this), ips[ip].result);
				}
				else{
					ips[ip].doms.push($(this));
				}
			}
			else{
				ips[ip] = {
					doms: [ $(this) ],
					result: false,
					xhr: $.ajax({
						url: Core.ROOT_URL + 'phpwhois/lookup?q=' + ip,
						type: 'json',
						success: function(r){
							var i;
							for(i in ips[ip]['doms']){
								build_fancy_overlay(r);
								render_fancy_ip(ips[ip]['doms'][i], r);
							}
							ips[ip].result = r;
						}
					})
				};
			}
		})
		.hoverIntent({
			over: function(){
				$(this).find('.ip-tooltip').slideDown(100);
			},
			out: function(){
				$(this).find('.ip-tooltip').slideUp(100);
			},
			timeout: 200
		});
});