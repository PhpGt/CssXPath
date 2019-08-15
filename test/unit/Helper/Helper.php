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
	
	<nav class="c-menu main-selection">
		<input type="checkbox" id="toggle-menu" />
		<label for="toggle-menu">
			<span>Menu</span>
		</label>
		
		<ul>
			<li>
				<a href="/">Home</a>
			</li>
			<li class="selected">
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
	<p>I'm a paragraph, but I'm not part of the article.</p>
	
	<article>
		<header>
			<h1>
				<a href="/blog/2018/04/27/first-example-article-title">
					First example article title
				</a>
			</h1>
			<time datetime="2018-04-27 02:24:00">27th April 2018</time>
		</header>
		<div class="content this-is-a-test" data-categories="example test blog-test" data-test-thing="my_test">
			<p>Example article paragraph 1.</p>
			<p>Example article paragraph 2.</p>
			<p>Example article paragraph 3.</p>
		</div>
		<div class="details" data-test-thing="another-test">
			<p>Here are some details: 12345</p>
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
			<span data-ga-client="(Test) Message, this has a comma">Your message</span>
			<textarea></textarea>
		</label>
		<label>
			<span>Spam me with marketing?</span>
			<input type="checkbox" name="marketing" checked />
		</label>
		<button name="do" value="contact">Send</button>
	</form>
</footer>

</body>
</html>
HTML;

	const HTML_SELECTS = <<<HTML
<!doctype html>
<form>
	<label>
		<span>From:</span>
		<select name="from">
			<option>0</option>		
			<option>1</option>		
			<option>2</option>		
			<option>3</option>		
			<option>4</option>		
			<option>5</option>		
		</select>
	</label>
	
	<label>
		<span>To:</span>
		<select name="to">
			<option>5</option>		
			<option>6</option>		
			<option>7</option>		
			<option>8</option>		
			<option>9</option>		
			<option>10</option>		
		</select>
	</label>
</form>
HTML;


}