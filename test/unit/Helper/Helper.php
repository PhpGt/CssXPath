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
			<span>Your message</span>
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

	const HTML_FORM = <<<HTML
<!doctype html>
<form method="post">

	<label>
		<span>Name</span>
		<input name="name" required />
	</label>
	<p>
		<span>Gender</span>
	</p>
	<label>
		<span>Male</span>
		<input type="radio" name="gender" value="m" />
	</label>
	<label>
		<span>Female</span>
		<input type="radio" name="gender" value="f" checked />
	</label>
	<label>
		<span>Other</span>
		<input type="radio" name="gender" value="o" />
	</label>
	
	<label>
		<span>Age range</span>
		<select name="age">
			<option value="<18" disabled>0-17</option>
			<option value="18-35">18-35</option>	
			<option value="36-60" selected>36-60</option>	
			<option value=">60">60+</option>
		</select>
	</label>
	<p>
		<span>Interests</span>
	</p>
	<label>
		<span>Baking</span>
		<input type="checkbox" name="interest[]" value="baking" />	
	</label>
	<label>
		<span>Colouring</span>
		<input type="checkbox" name="interest[]" value="colouring" checked />	
	</label>
	<label>
		<span>DIY</span>
		<input type="checkbox" name="interest[]" value="diy" />	
	</label>
	<label>
		<span>Knitting</span>
		<input type="checkbox" name="interest[]" value="knitting" checked />	
	</label>
	<label>
		<span>Photography</span>
		<input type="checkbox" name="interest[]" value="photography" checked />	
	</label>
</form>
HTML;


}