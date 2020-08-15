<style type="text/css">
 a.swift-upgrade-container {
	text-decoration: none;
 }

 .swift-upgrade-container h3 {
	 background: transparent !important;
	 margin: 0 auto !important;
	 border: none !important;
       padding: 10px 0;
 }

 .swift-upgrade-container h4 {
	 font-weight: 700 !important;
 }

 .swift-upgrade-container h5 {
	font-size: 1.2em;
	margin: 0;
	padding: 0;
 }


 .swift-upgrade-table li {
	width: 100% !important;
 }

 .swte-money-back-guarantee {
	height: 70px !important;
 }

 .swte-money-back-guarantee img {
	height: 80px !important;
 }

 .swte-money-back-col {
	width: 100%;
	font-size: 20px !important;
 }

 .swift-dashboard-item .swte-money-back-guarantee{
	display: none;
 }

 .swift-upgrade-inner {
	display: inline-block;
	width: calc(55% - 20px);
	vertical-align: top;
	padding: 0 10px;
 }

.swift-upgrade-container .swift-upgrade-table li {
	display: none;
 }

</style>

<script>
(function(){
	var ad_interval, ad_timeout;

	function show_ad(){
		var list = jQuery(".swift-upgrade-table li:not(.active)").toArray();
		jQuery(".swift-upgrade-table li.active").hide().removeClass('active');
		jQuery(list[Math.floor(Math.random()*list.length)]).addClass('active').fadeIn(1200);
	}

	jQuery(document).on('hover','.swift-upgrade-table', function(){
		clearInterval(ad_interval);
	});

	jQuery(document).on('mouseleave','.swift-upgrade-table', function(){
		clearTimeout(ad_timeout)
		ad_timeout = setTimeout(function(){
			clearInterval(ad_interval);
			show_ad();
			ad_interval = setInterval(show_ad,5000);
		}, 2000);
	});

	jQuery(function(){
		clearInterval(ad_interval);
		show_ad();
		ad_interval = setInterval(show_ad,5000);
	});
})();
</script>

<a class="swift-section-container swift-table swift-upgrade-container" target="_blank" href="<?php echo Swift_Performance_Lite::upgrade_link();?>">
	<h3><?php esc_html_e('Get Swift Performance PRO!', 'swift-performance');?></h3>
	<h5>1000+ users upgraded to PRO in last 12 months</h5>
	<ul class="swift-upgrade-table">
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/compute-api.png">
			<span>
				<h4><?php esc_html_e('Better Critical CSS', 'swift-performance');?></h4>
				<?php esc_html_e('Compute API can speed up CPU extensive processes and generate even 400% smaller Critical CSS.', 'swift-performance');?>
			</span>
		</li>
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/image-optimization.png">
			<span>
				<h4><?php esc_html_e('Unlimited Image Optimizer', 'swift-performance');?></h4>
				<?php esc_html_e('Swift Performance Pro comes with a built-in, unlimited image optimizer. You can lossy/losslessly optimize your JPEG and PNG images, and generate WebP version using our Image Optimizer API.', 'swift-performance');?>
			</span>
		</li>
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/youtube-smart-embed.png">
			<span>
				<h4><?php esc_html_e('Youtube Smart Embed', 'swift-performance');?></h4>
				<?php esc_html_e('With smart Youtube embed feature the browser won’t load unnecessary assets until the visitor start the video (or before the embed is in the viewport on mobile), but provide the same user experience.', 'swift-performance');?>
			</span>
		</li>
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/server-push.png">
			<span>
				<h4><?php esc_html_e('Server Push', 'swift-performance');?></h4>
				<?php esc_html_e('If your site is running on HTTP2 this feature will help to optimize resources, and speed up page load.', 'swift-performance');?>
			</span>
		</li>
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/proxy-caching.png">
			<span>
				<h4><?php esc_html_e('Proxy Caching', 'swift-performance');?></h4>
				<?php esc_html_e('Proxy caching is an advanced option if your site is using proxies like Cloudflare. It can extremely decrease the TTFB behind the proxy server.', 'swift-performance');?>
			</span>
		</li>
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/no-ads.png">
			<span>
				<h4><?php esc_html_e('No Ads', 'swift-performance');?></h4>
				<?php esc_html_e('While Swift Performance Lite contains advertisments, all of our premium plans are ad free. Upgrade now if you don\'t like ads, or if you would like to provide more professional service for your clients.', 'swift-performance');?>
			</span>
		</li>
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/support.png">
			<span>
				<h4><?php esc_html_e('Extended Support', 'swift-performance');?></h4>
				<?php esc_html_e('We provide an extended support for all paid plans. If you have compatibility issues, or even if you need only help with configuration we are happy to help.', 'swift-performance');?>
			</span>
		</li>
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/lazyload-elements.png">
			<span>
				<h4><?php esc_html_e('Lazyload elements – ajaxify', 'swift-performance');?></h4>
				<?php esc_html_e('Lazyload elements is an advanced feature. With this you can specify content parts which should be loaded via AJAX after the page was loaded. Thanks for this feature you don’t need to exclude whole page just because there is a small dynamic part.', 'swift-performance');?>
			</span>
		</li>
		<li>
			<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/many-more.png">
			<span>
				<h4><?php esc_html_e('Optimize Fonts', 'swift-performance');?></h4>
				<?php esc_html_e('Optimizing fonts can speed up page rendering, and decrease fully loaded time. Swift Performance PRO can automatically preload fonts which are used on the page.', 'swift-performance');?>
			</span>
		</li>
	</ul>
	<div class="swte-money-back-guarantee">
		<img src="<?php echo SWIFT_PERFORMANCE_URI?>images/money-back-guarantee.png">
		<div class="swte-money-back-col">
			<?php esc_html_e('100% money back guarantee', 'swift-performance');?>
			 <small><?php esc_html_e('We provide 14 days money back guarantee.', 'swift-performance');?></small>
		</div>
	</div>
</a>
