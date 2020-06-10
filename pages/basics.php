<!DOCTYPE html>
<html>

<head>
    <title>Basics</title>
    <?php include('../../base/pageHeader.html') ?>
</head>

<body class="lightContainer">
    <?php include('../../base/pageBodyStart.html') ?>

    <h1 id="title">The Basics</h1>
    <hr>
    <p>Let's cover all the basic concepts that ABParser is built upon.</p>

    <h2 id="tokens">Tokens</h2>
    <hr>
    <p>
        The most fundamental things when it comes to using ABParser are the tokens.<br><br>
        Essentially, a token is a specific piece of text you want ABParser to look out for. For example, in JSON it would be the quotes, braces, commas and colons.<br><br>
        So, what happens is that when you create an ABParser, you choose all of the tokens that ABParser will look out for. And, when ABParser hits that token, it will notify you, and you can handle it.<br><br>
    </p>

    <h2 id="trivia">Trivia</h2>
    <hr>
    <p>
        But then what happens to everything that <i>isn't</i> a token? Well, it becomes trivia, or more specifically the leading and trailing.<br><br>
        So, when ABParser encounters a token, you can then look at the trivia around it - that's how it works.<br><br>
        Trivia is split down into two parts - the leading and trailing. When ABParser encounters a token and notifies you, you'll have access to both of these.
    </p>

    <h3 id="leading">Leading</h3>
    <hr>
    <p>
        The leading is the text that came before the current token, starting at the last token.<br><br>
        If this is the first token in the string, then it will be starting from the beginning of the text.<br><br>
        In the diagram below, in red are the tokens, and in green is the leading.<br><br>
    </p>
    <img src="../pageImages/usageBasics/LeadingDiagram.png">

    <h3 id="trailing">Trailing</h3>
    <hr>
    <p>
        The trailing is the opposite - it is the text AFTER the token going up towards the next token.<br><br>
        And this right here is a sign of how powerful ABParser is. You can look ahead of where you are with no effort!<br><br>
        If this is the last token in the string, then the will end at the end of the text.<br><br>
        Once again, the diagram below shows what the trailing is - using red at the tokens and the green at the trailing.<br><br>
    </p>
    <img src="../pageImages/usageBasics/TrailingDiagram.png">

    <h2 id="limits">Limits</h2>
    <hr>
    <p>
        And, already we know almost all of ABParser. Now, if you want to build a full parser with ABParser, you need to know about the limits that ABParser provides.<br><br>
        There are three different limits in ABParser, and they limit ABParser to only pay attention to certain tokens or characters. These are all set as the parser runs.
    </p>

    <table class="docs-table">
		<tr>
			<th>Name</th>
			<th>Function</th>
		</tr>
		<tr>
			<td>TokenLimit</td>
			<td>This will tell ABParser to <i>only</i> listen out for a specific sub-set of the tokens, used very commonly.</td>
		</tr>
        <tr>
			<td>TriviaLimit</td>
			<td>This will tell ABParser to <i>ignore</i> certain characters when generating trivia.</td>
		</tr>
        <tr>
			<td>DetectionLimit</td>
			<td>This will tell ABParser to <i>ignore</i> certain characters when scanning for tokens.</td>
		</tr>
	</table>

    <h3 id="tokenLimit-title">TokenLimit</h3>
    <hr>
    <p>
        This is the most common limit, you'll need this to parse most things.<br><br>
        So a <code>TokenLimit</code> is <i>a list of tokens</i>, and we give it a name that we can refer to later, then we can switch to that <code>TokenLimit</code> by name while the parser is running and it will only listen out for those tokens.<br><br>
        Unlike all the other limits this one is <b>stack-based</b>, which means the current tokenLimits are stored as a stack, and whatever is at the top of the stack is what <code>TokenLimit</code> we're currently in.<br><br>
        We can <i>enter</i> into a TokenLimit, which will <i>push</i> that TokenLimit onto the stack, and we can <i>exit</i> a TokenLimit, which will <i>pop</i> that TokenLimit off the top of the stack.<br><br>
        Of course, when there is nothing on the stack, there is <i>no</i> TokenLimit, so ABParser will just scan for all the tokens.
    </p>
    <img src="../pageImages/usageBasics/tokenLimitStack.png">

    <h3 id="tokenLimit-title">TriviaLimit</h3>
    <hr>
    <p>
        This can useful at times, with this we can get ABParser to ignore certain characters in the trivia.<br><br>
        That way the <code>Leading</code> and <code>Trailing</code> won't contain those characters, this could be useful to exclude something such as whitespace from the trivia, if it would get in the way.<br><br>
        This is not <i>stack-based</i> like the <code>TokenLimit</code> you can just set a <code>TriviaLimit</code> and unset it again.
    </p>

    <h3 id="detectionLimit-title">DetectionLimit</h3>
    <hr>
    <p>
        If you want, you can get ABParser to ignore certain characters when scanning for tokens. For example, you can set a <code>DetectionLimit</code> to whitespace, and that means if you have a token that's "the", if they have "t h e" in the text, it will still be detected.<br><br>
        There are not many places you would want to use this. However, one example is in pre-processor definitions. Because pre-processor definitions can be written as:
    </p>

    <pre><code>
#define ABC
#    define ABC
    #define ABC
    </code></pre>

    <p>
        All of these are valid. The "#" has to always be at the start of the line, but it can be indented forwards.<br><br>
        So, to achieve this, we could make the "#" token become "\n#", that way it will only work at the start of a line, then tell ABParser to ignore <i>all</i> whitespace (excluding the '\n'), that way indenting it forward with whitespace would work.<br><br>
        And everything is handled for you!
    </p>

    <h2 id="next">Next</h2>
    <hr>
    <p>That's it! That's all there is to it: the tokens, trivia and limits! Now, we're ready to start writing some code, so, to do that, simply choose what part of ABParser you want to use, and we'll guide you from there!</p>

    <div class="navBoxContainer">
        <div data-navigates="alongside" data-navigateTo="Managed" class="navBox navBoxLight navBox-half">
            <h1 class="noAnchor">Managed</h1>
            <p>The managed side of ABParser - for C#.</p>
        </div>
        <div data-navigates="alongside" data-navigateTo="Unmanaged" onclick="NavigateToChild('Unmanaged', 'usage-unmanaged.php')" class="navBox navBoxLight navBox-half">
            <h1 class="noAnchor">Unmanaged</h1>
            <p>The unmanaged side of ABParser - for C-like languages.</p>
        </div>
    </div>

    <?php include('../../base/pageBodyEnd.html') ?>
</body>
</html>