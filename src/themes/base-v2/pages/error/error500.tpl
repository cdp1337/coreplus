{css src="assets/css/error500.css"}{/css}
{script src="assets/js/json-sans-eval.js" location="head"}{/script}
{script src="assets/js/css-browser-selector.js" location="head"}{/script}
{script src="assets/js/game-of-life-v3.1.1.js" location="head"}{/script}


<div class="error-container">
	<h2>Server Error 500 &ndash; He's dead, Jim!</h2>
	<h3>Why not play a game while the network engineers fix whatever blew up?</h3>
	<p>Or you can {a history="1"}go back{/a}. Studies show it sure beats mashing the f5 key.</p>
</div>

<div class="gol-wrapper">
	<canvas id="canvas" height=""></canvas>
	<div class="ui-container">
		<div class="ui-center">
			<div class="box gol-controls">
				<div class="subtitle">Controls</div>
				<form action="">
					<input type="button" value="Run" id="buttonRun" title="Key: R" />
					<input type="button" value="Step" id="buttonStep" title="Key: S" />
					<input type="button" value="Clear" id="buttonClear" title="Key: C" />
					<input type="button" value="Share" id="buttonExport" />
					<span id="exportUrl"><a id="exportUrlLink">Link</a> | <a id="exportTinyUrlLink" title="Tiny URL">Create micro URL</a></span>
				</form>
			</div>
			{literal}
				<div class="box patterns">
					<div class="subtitle">Patterns</div>
					<div class="button"><a href="?autoplay=0&amp;trail=0&amp;grid=1&amp;colors=1&amp;zoom=1&amp;s=%5B{%228%22:%5B60,61,98,103,109,115%5D},{%229%22:%5B60,61,77,78,97,99,102,104,108,110,114,116%5D},{%2210%22:%5B76,79,98,103,105,109,111,115,117%5D},{%2211%22:%5B76,79,104,110,112,116,118%5D},{%2212%22:%5B60,61,63,64,77,78,111,117%5D},{%2213%22:%5B60,61,63,64%5D},{%2219%22:%5B76,77,79,97,98,102,103,108,109,114,115%5D},{%2220%22:%5B76,78,79,97,99,102,104,108,110,114,116%5D},{%2221%22:%5B98,103,105,109,111,115,117%5D},{%2222%22:%5B104,110,112,116,118%5D},{%2223%22:%5B61,111,117%5D},{%2224%22:%5B60,62,76,77%5D},{%2225%22:%5B60,62,75,78%5D},{%2226%22:%5B61,76,79%5D},{%2227%22:%5B77,78,96,97,102,103,109,110,115,116%5D},{%2228%22:%5B96,98,102,104,109,111,115,117%5D},{%2229%22:%5B61,65,97,98,103,105,110,112,116,118%5D},{%2230%22:%5B60,62,64,66,104,105,111,113,117,119%5D},{%2231%22:%5B60,62,64,66,75,76,112,113,118,120%5D},{%2232%22:%5B61,65,75,78,119,120%5D},{%2233%22:%5B77,78%5D},{%2237%22:%5B78,79%5D},{%2238%22:%5B77,79%5D},{%2239%22:%5B77%5D},{%2240%22:%5B60,61,63,64,75,77%5D},{%2241%22:%5B61,63,75,76%5D},{%2242%22:%5B61,63%5D},{%2243%22:%5B60,61,63,64,114%5D},{%2244%22:%5B78,79,84,85,92,93,95,113,115%5D},{%2245%22:%5B79,84,86,92,93,95,96,97,104,112,115%5D},{%2246%22:%5B78,86,98,103,105,111,113,114%5D},{%2247%22:%5B75,77,86,87,92,93,95,96,97,102,105,110,112%5D},{%2248%22:%5B75,76,93,95,103,104,109,112%5D},{%2249%22:%5B93,95,110,111%5D},{%2250%22:%5B94%5D}%5D" title="Still Life Patterns">Still Life</a></div>
					<!-- <div class="button"><a href="" title="">Oscillators</a></div> -->
					<div class="button"><a href="?autoplay=0&amp;trail=0&amp;grid=1&amp;colors=1&amp;zoom=1&amp;s=%5B{%229%22:%5B44%5D},{%2210%22:%5B42,44%5D},{%2211%22:%5B32,33,40,41,54,55%5D},{%2212%22:%5B31,35,40,41,54,55%5D},{%2213%22:%5B20,21,30,36,40,41%5D},{%2214%22:%5B20,21,30,34,36,37,42,44%5D},{%2215%22:%5B30,36,44%5D},{%2216%22:%5B31,35%5D},{%2217%22:%5B32,33%5D}%5D" title="Gosper Glider Gun">Gosper Glider Gun</a></div>
					<!-- <div class="button"><a href="" title="">Guns</a></div> -->
					<div class="button"><a href="?autoplay=0&amp;trail=0&amp;grid=1&amp;colors=1&amp;zoom=1&amp;s=%5B{%2239%22:%5B110%5D},{%2240%22:%5B112%5D},{%2241%22:%5B109,110,113,114,115%5D}%5D" title="Acorn Patter">Acorn</a></div>
					<div class="button"><a href="?s=random" title="Random Pattern">Random</a></div>
				</div>
			{/literal}
			<div class="box runtime last">
				<p class="info">
					<abbr title="Current Generation">Generation</abbr>: <span id="generation"></span> | <abbr title="Number of live cells">Live cells</abbr>: <span id="livecells"></span> | <abbr title="Execution times: Algorithm / Canvas (Algorithm / Canvas Averages)">Step time</abbr>: <span id="steptime"></span> ms
				</p>
			</div>
			<div class="box layout">
				<div class="subtitle">Layout</div>
				<form>
					<input type="button" id="buttonTrail" value="Trail"/>
					<input type="button" id="buttonGrid" value="Grid" />
					<input type="button" id="buttonColors" value="Colors" />
					<span id="layoutMessages"></span>
				</form>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<span class="author">{a target="_blank" href="http://pmav.eu"}Game of Life{/a}</span>

	<div class="hint-container">
		<span id="hint">Hint: hit the <strong>Run</strong> button!</span>
	</div>
	<div class="clear"></div>
</div>