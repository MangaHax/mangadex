<div role="tabpanel" class="tab-pane fade show active" id="md_at_home">
	<p>MangaDex@Home is a P2P (peer-to-peer) system where users will be able to volunteer the usage of either their personal computers or servers to act as cache server nodes to alleviate the stress on our own cache servers. Over time, we envisage that the majority, if not all, of the older chapters will be served by MangaDex@Home.</p>

	<p>You will be hosting a client that acts as a P2P system for older chapters. Basically, your machine will act as a server where a tiny portion of older MangaDex chapters will be stored and when a reader wants to read an older chapter, it will be "fetched" from your machine and served to the reader. If you're familiar with torrenting, it is very similar: torrents work by sharing parts of the whole file to other torrent clients in order to ensure download stability and faster speeds.</p>

	<p>Since your machine will basically function as a server for MD, we are asking only people who meet or exceed the following criteria run a MangaDex@Home client for now:

	<ul>
		<li>An IPv4 address (static or dynamic).</li>
		<li>A minimum network speed of 80Mbps up/down.</li>
		<li>A minimum of 40GB of dedicated storage space, preferably more.</li>
		<li>24/7 availability (This means the machine must be *on* 24/7).</li>
	</ul>
	
	<p>You will be expected to provide an up to date <a href="https://www.speedtest.net">speedtest.net</a> result on request to validate your specs as part of the application process, whether you are in the initial batch or later on.</p>
	
	<strong>Note that if you are concerned about the legality of hosting MangaDex's content, consider renting a dedicated server or a virtual private server (VPS) and installing the MangaDex@Home client there. If you still have concerns about legality, we suggest that you do not host the MangaDex@Home client at all.</strong>
	
	<?php if ($templateVar['user']->premium || $templateVar['user']->get_chapters_read_count() > MINIMUM_CHAPTERS_READ_FOR_SUPPORT) : ?>
		<h3 class="mt-4">Update for people who are interested in hosting a MangaDex@Home client</h3>
		<p>MangaDex@Home has been highly successful, and now all images from the archives load from the <a href="https://mangadex.network/" target="_blank">MangaDex Network</a>. We have come to the point where there are not enough people applying for clients, either because they do not meet the minimum bandwidth requirements, or they don't know much about server management or running java programs. The minimum requirements for hosting a client will drop in future, but we still need more high quality clients before that is possible.</p>
		<p>Until now, the only options for people who were interested in hosting a client have been to either host on their own computer or rent a server to do so. Both options require some knowledge of how to run a java program and also some server management (if you rented a server).</p>
		<p>A third option is now available via our affiliates at <a href="https://sdbx.moe" target="_blank">sdbx.moe</a>. It is now possible to run a client with absolutely no knowledge of server management or how to run a java program. Simply subscribe to a VPS <a href="https://sdbx.moe/vps" target="_blank">here</a>, provide your client secret, and your client will run for as long as your subscription is active.</p>
		<p>The VPS plans are suitable for running a client, because they run on super fast NVMe SSDs and come with a dedicated IP address. The cheapest plan, at $6.25 a month, meets the minimum requirements of 40 GB storage and 80 Mbps up/down. Both FR and CA locations are available, but we recommend that you go for CA because we urgently need more clients in North America. </p>
		<p>Similar to the subscriptions that were available last year, you can simply subscribe to a VPS, provide your client secret, and then forget about it. Your client will be active for as long as your subscription is active.</p>
		<p>Detailed instructions for this process can be found <a href="/md_at_home/request">here</a>.</p>
	
	<?php endif; ?>
	
</div>