<!DOCTYPE html>
<html>

<head>
    <title>Token Limits</title>
    <?php include('../../base/pageHeader.html') ?>
</head>

<body class="lightContainer">
    <?php include('../../base/pageBodyStart.html') ?>

    <h1 id="title">Limits + BeforeTokenProcessed</h1>
    <hr>
    <p>
       Now, let's look at how we can apply <i>limits</i> in our code.
    </p>

    <h2 id="btp">BeforeTokenProcessed</h2>
    <hr>
    <p>
        Now, the limits must be set inside something called the <i>BeforeTokenProcessed</i>, otherwise you may get strange results you're not expecting.<br><br>
        As you know, <code>OnTokenProcessed</code> gives us the leading, trailing and token. But, in order to generate the trailing, what ABParser technically does is parses ahead in the background up towards the next token before <b>then</b> triggering <code>OnTokenProcessed</code>.<br><br> 
        While, <code>BeforeTokenProcessed</code> gets called <b>instantly</b> as soon as we hit the token, before it scans ahead to get the next token and trailing.<br><br>
        We must set the limits in here, because if we don't, the parser will have already determined what the next token and trailing will be, and it will be too late to limit what it does next.<br><br>
        To use the <code>BeforeTokenProcessed</code>, simply override <code>BeforeTokenProcessed</code>, and you'll find you have almost all the same things as the <code>OnTokenProcessed</code>, but not the trailing or next token.
    </p>

    <pre><code class="language-csharp">
protected override void BeforeTokenProcessed(BeforeTokenProcessedEventArgs args) { }
    </code></pre>

    <div class="msgBox infoBox">
		<h4 class="noAnchor">Information</h4>
		<p>Limits can also be set in <code>OnStart</code> too if necessary.</p>
	</div>

    <h2 id="tokenLimit-title">TokenLimit</h2>
    <hr>
    <p>Let's start by looking at how to use a TokenLimit in the code, to remind you, here's how its stack-based system works:</p>

    <img src="../pageImages/usageBasics/tokenLimitStack.png">

    <h3 id="tokenLimit-addingLimits">Adding Limits</h3>
    <hr>
    <p>
        They're just like with the regular tokens, they require a bit of processing, so, we like to generate them once at application start, and leave them there.<br><br>
        Now, in order to remove clutter, we configure them alongside the tokens.<br><br>
        To do this, right after the <code>new ABParserToken(params)</code>, we can do <code>.AddToLimit(limitNames)</code>.<br><br>
        Then, within the brackets we simply give it a name, e.g. <code>new ABParserToken(params).AddToLimit("Limit")</code>. You can also add them to multiple limits either by chaining multiple <code>AddToLimit</code> methods, or by simply providing multiple strings to the method (recommended), like <code>AddToLimit("Limit1", "Limit2")</code>.<br><br>
        Below is an example, which will create three different <code>TokenLimit</code>s, <code>QuoteLimit</code>, with the quote token in it, <code>QuestionLimit</code>, with the question token in it, and <code>BothLimit</code>, which contains both:
    </p>

    <pre><code class="language-csharp">
static readonly ABParserConfiguration ParserConfig = ABParserConfiguration.Create(new ABParserToken[]
{
    new ABParserToken(new ABParserText("QUOTE"), new ABParserText("'")).AddToLimit("QuoteLimit", "BothLimit"),
    new ABParserToken(new ABParserText("QUESTION"), new ABParserText("?")).AddToLimit("QuestionLimit", "BothLimit")
});
    </code></pre>

    <h3 id="tokenLimit-using">Using Limits</h3>
    <hr>

    <p>
        We call <code>EnterTokenLimit(limitName)</code> to <i>enter</i> into a TokenLimit, and <code>ExitTokenLimit</code> to <i>exit</i> one.
        If we do the following:
    </p>

    <pre><code class="language-csharp">
EnterTokenLimit("Limit1");
EnterTokenLimit("Limit2");
    </code></pre>

    <p>
        We just pushed <code>Limit1</code> onto the TokenLimit's stack, then pushed <code>Limit2</code> onto the TokenLimit's stack, so <code>Limit2</code> is at the top.<br><br>
        That means that we're inside the limit <code>Limit2</code>, meaning whatever tokens are in the <code>Limit2</code> are the ones we'll be listening out for.<br><br>
        However, if we then do:
    </p>

    <pre><code class="language-csharp">
ExitTokenLimit();
    </code></pre>

    <p>
        We've now taken the <code>Limit2</code> off the stack, so now we're limited to whatever is in <code>Limit1</code>. And, if we call <code>ExitTokenLimit</code> yet again, then we'll have removed everything off the stack and ABParser will go back to normal without any limits on what tokens it detects.<br><br>
        Of course, remember that <i>all</i> these things need to be called from the <code>BeforeTokenProcessed</code>.
    </p>

    <h2 id="tokenLimit-using">TriviaLimit</h2>
    <hr>
    <p>
        The <code>TriviaLimit</code> can be used to exclude certain characters from the trivia.<br><br>
        Typically, you want to exclude whitespace when you're <i>not</i> inside a string literal, as the whitespace can get in the way of checking the trivia for more information.
    </p>

    <h3 id="tokenLimit-addingLimits">Adding Limits</h3>
    <hr>
    <p>
        Adding <code>TriviaLimit</code>s is also done on the <code>ABParserConfiguration</code>, to save it being re-processed repeatedly as the parser is constructed. We add limits by chaining them off the end off the <code>ABParserConfiguration.Create</code> we already have.<br><br>
        
    </p>
    
    <?php include('../../base/pageBodyEnd.html') ?>
</body>
</html>