<?php
namespace Gt\CssXPath\Test\Helper;

class Helper {
	const HTML_SIMPLE = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>HTML Simple</title>
</head>
<body>
	<h1 id="the-title">HTML Simple</h1>
	<p>This is a <em>very</em> simple HTML document for testing the basics.</p>
</body>
</html>
HTML;

	const HTML_COMPLEX = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>HTML Complex</title>
</head>
<body>

<header>
	<h1 class="c-logo">
		<a href="/">
			<span>Site logo</span>
		</a>
	</h1>
	
	<nav class="c-menu">
		<input type="checkbox" id="toggle-menu" />
		<label for="toggle-menu">
			<span>Menu</span>
		</label>
		
		<ul>
			<li class="selected">
				<a href="/">Home</a>
			</li>
			<li>
				<a href="/blog">Blog</a>
			</li>
			<li>
				<a href="/about">About</a>
			</li>
			<li>
				<a href="/contact">Contact</a>
			</li>
		</ul>
	</nav>
</header>

<main>
	<article>
		<header>
			<h1>
				<a href="/blog/2018/04/27/first-example-article-title">
					First example article title
				</a>
			</h1>
			<time datetime="2018-04-27 02:24:00">27th April 2018</time>
		</header>
		<div class="content">
			<p>Example article paragraph 1.</p>
			<p>Example article paragraph 2.</p>
			<p>Example article paragraph 3.</p>
		</div>
	</article>
</main>

<footer>
	<form method="post">
		<label>
			<span>Your name</span>
			<input name="your-name" placeholder="e.g. John Smith" required />
		</label>
		<label>
			<span>Your email address</span>
			<input name="email" type="email" placeholder="e.g. j.smith@email.com" required />
		</label>
		<label>
			<span>Your mesage</span>
			<textarea></textarea>
		</label>
		<button name="do" value="contact">Send</button>
	</form>
</footer>

</body>
</html>
HTML;


}