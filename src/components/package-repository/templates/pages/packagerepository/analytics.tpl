{script library="chartist.js"}{/script}
{script library="jquery"}{/script}
{css src="css/package-repository.css"}{/css}


<section class="time-wrapper">
	<div id="time-graph" class="time-graph"></div>
</section>

<section class="versions-wrapper">
	<header class="versions-header">
		{t 'STRING_CORE_PLUS_VERSIONS'}
	</header>
	<div id="versions" class="versions-graph"></div>
	<ul class="versions-text">
		{foreach $useragents as $dat}
			<li title="{$dat.title|escape}" class="{$dat.class}">{$dat.useragent}: {$dat.value} Total Hits</li>
		{/foreach}
	</ul>
</section>

<section class="hosts-wrapper">
	<header class="hosts-header">
		{t 'STRING_SERVERNAMES_AND_VERSIONS'}
	</header>
	<table class="listing">
		<tr>
			<th>{t 'STRING_SERVERNAME'}</th>
			<th>{t 'STRING_IP_ADDRESS'}</th>
			<th>{t 'STRING_LATEST_VERSION'}</th>
			<th>{t 'STRING_LATEST_UPDATE'}</th>
		</tr>
		{foreach $hosts as $rec}
			<tr>
				<td>
					{if $rec.servername == $rec.ip_addr}
						N/A
					{else}
						{$rec.servername}
					{/if}
				</td>
				<td>{geoiplookup $rec.ip_addr} {$rec.ip_addr}</td>
				<td>{$rec.version}</td>
				<td>{date $rec.datetime format="SDT"}</td>
			</tr>
		{/foreach}
	</table>
</section>



{script location="foot"}<script>
	$(function() {
		new Chartist.Pie(
			// Target
			'#versions',
			// Data
			{$chart.versions},
			// Options
			{
				/*labelInterpolationFnc: function(value) {
				 return value[0]
				 }*/
			},
			// Responsive Options
			[
				[
					'screen and (min-width: 640px)',
					{
						chartPadding:   60,
						labelOffset:    100,
						labelDirection: 'explode'
					}
				],
				[
					'screen and (min-width: 1024px)',
					{
						chartPadding:   60,
						labelOffset:    80,
						labelDirection: 'explode'
					}
				]
			]
		);

		new Chartist.Line(
			// Target
			'#time-graph',
			// Data
			{$chart.all},
			// Options
			{
				fullWidth:    true,
				chartPadding: {
					right: 40,
					top:   40
				}
			}
		);

		$('#versions').tooltip();
		$('#time-graph').tooltip();

		// Add a title 
		/*$('#versions').on('mouseover', 'g.ct-series', function() {
		 console.log($(this));
		 });*/
	});
</script>{/script}