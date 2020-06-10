<!DOCTYPE html>
<html>

<head>
    <title>Creating an ABParser</title>
    <?php include('../../base/pageHeader.html') ?>
</head>

<body class="lightContainer">
    <?php include('../../base/pageBodyStart.html') ?>

    <h1 id="title">Creating an ABParser</h1>
    <hr>
    <p>
        Let's start writing code. On this page, we'll create a simple parser with a few tokens, and we'll then run it with a little test string.
    </p>

    <h2 id="creation">Creation</h2>
    <h3 id="creatingParser">Creating the parser</h3>
    <hr>

    <p>
        First, we have to create the parser that we'll put everything into!<br><br>
        So, to create an ABParser you simply create a class and make it inherit <code>ABParser</code>, like below - that's all it takes!<br><br>
        We'll call this one <code>DemoParser</code>.
    </p>

    <pre><code class="language-csharp">
public class DemoParser : ABParser { }
    </code></pre>

    <h3 id="tokens">Tokens</h3>
    <h4 id="tokensArray">Token Array</h4>
    <hr>
    <p>
        Next, we need our tokens. There's no good in making an empty parser!<br><br>
        To do this, we need to create an object called the <code>ABParserConfiguration</code>. However, we only want to create this once! Because it does some processing, and we don't want to do that everytime we construct the parser.<br><br>
        So, we'll make a static, readonly, field within our parser that's of type <code>ABParserConfiguration</code>, and we'll just call it <code>ParserConfig</code>. There's no point in making it public, so, we'll keep it private.<br><br>
        Then, we need to set this. We'll set it right as we declare it; We'll set it to <code>ABParserConfiguration.Create</code>, then pass in our tokens as an array (<code>ABParserToken[]</code>)<br><br>
        We'll worry about adding tokens in a moment, but let's just keep it blank for now. Finally, in order to tell ABParser about those tokens, we just implement the constructor, but inheriting the constructor from the base one, passing in the <code>ParserConfig</code>.<br><br>
        So, it will look like this:
        
    </p>
    <pre><code class="language-csharp">
public class DemoParser : ABParser
{
    static readonly ABParserConfiguration ParserConfig = ABParserConfiguration.Create(new ABParserToken[] {
        // ...Tokens go here...
    });

    public DemoParser() : base(ParserConfig) { }
}
    </code></pre>

    <h4 id="tokensAdd">Adding Tokens</h4>
    <hr>
   
    <p>Next, let's put some tokens in the array. All of the tokens are comprised of three parts:</p>

    <ul>
        <li>Name - ABParser does nothing with this, it is purely for you to distinguish tokens.</li>
        <li>Contents - This is what the token is actually made up of.</li>
        <li>Tag - This is some data you can attach if you want, ABParser does nothing with this. </li>
    </ul>

    <p>
        In order to create a token, we call the constructor of <code>ABParserToken</code>, and pass in the name and contents (and optionally the tag) - but, you'll notice that the name and contents are <code>ABParserText</code> objects.<br><br>
        The <code>ABParserText</code> object represents text in ABParser, and it has <i>three</i> constructors on it, one takes in a string, another a character array and another a StringBuilder.<br><br>
        Essentially, an <code>ABParserText</code> joins all of these three possibilities, so you can use whichever data type you prefer in there.<br><br>
        So, where you need to use an <code>ABParserText</code> object, we just call the constructor of it, passing in a string - however, if you wanted, you could pass in a character array and StringBuilder and that would work fine as well.<br><br>
        Below are some random tokens:
    </p>

    <pre><code class="language-csharp">
public class DemoParser : ABParser
{
    static readonly ABParserConfiguration ParserConfig = ABParserConfiguration.Create(new ABParserToken[] {
        new ABParserToken(new ABParserText("A"), new ABParserText("a")),
        new ABParserToken(new ABParserText("B"), new ABParserText("b")),
    });

    public DemoParser() : base(ParserConfig) { }
}
    </code></pre>

    <h3 id="notification">Notification</h3>
    <hr>
    <p>
        OK, now, we want to be told when we encounter a token so we can then do something about it.<br><br>
        So, to do that all we have to do is override the <code>OnTokenProcessed</code> method, and you'll find the Leading and Trailing all in there under the <code>args</code> parameter.<br><br>
        You'll also find a lot of other values in there, such as the previous and next token, as well as what position this token started at and what position it ended at!<br><br>
        The leading and trailing is given to you as an <code>ABParserText</code> object, so, in order to get something out of it, just take it and do <code>.AsSomething()</code>, replacing <code>Something</code> with whatever data type you want it in - a character array, string or StringBuilder.<br><br>
        Below you'll see the function definition being added:
    </p>

    <pre><code class="language-csharp">
public class DemoParser : ABParser
{
    static readonly ABParserConfiguration ParserConfig = ABParserConfiguration.Create(new ABParserToken[] {
        new ABParserToken(new ABParserText("A"), new ABParserText("a")),
        new ABParserToken(new ABParserText("B"), new ABParserText("b")),
    });

    public DemoParser() : base(ParserConfig) { }

    protected override void OnTokenProcessed(OnTokenProcessedEventArgs args) {
        // ...whatever happens per token goes here...
    }
}
    </code></pre>
    <h3 id="startEndNotification">OnStart + OnEnd</h3>
    <hr>
    <p>
        Of course, you may want to do something when you start parsing or reach the end of a parse - so, you can override the methods <code>OnStart</code> and <code>OnEnd</code>.<br><br>
        The <code>OnEnd</code> will give you some text with the <code>leading</code>, which is the text that lead up to the end starting from the last token.<br><br>
        So, below we have an ABParser that takes all of the leadings and adds them together into one string - so, that one string would end up with everything but the tokens.<br><br>
        Then, when we execute this parser, we can of course just access this <code>Result</code> variable to get back the result it gave us where we execute the parser.<br><br>
    </p>

    <div class="msgBox infoBox">
		<h4 class="noAnchor">Information</h4>
		<p> We set the <code>Result</code> to nothing in the <code>OnStart</code> because ABParsers are re-useable, so you can run them as many times as you want, without creating a new instance - so always be sure to reset your variables in there!</p>
	</div>

    <pre><code class="language-csharp">
public class DemoParser : ABParser
{
    public string Result = "";

    static readonly ABParserConfiguration ParserConfig = ABParserConfiguration.Create(new ABParserToken[] {
        new ABParserToken(new ABParserText("A"), new ABParserText("a")),
        new ABParserToken(new ABParserText("B"), new ABParserText("b")),
    });

    public DemoParser() : base(ParserConfig) { }

    override OnStart() {
        Result = "";
    }

    protected override void OnTokenProcessed(OnTokenProcessedEventArgs args) {
        Result += args.Leading.AsString();
    }

    override OnEnd(ABParserText leading) {
        Result += leading.AsString();
    }
}
    </code></pre>

    <p>Generally, you wouldn't use an ABParser for something as simple as this.</p>

    <h2 id="startEndNotification">Execution</h2>
    <hr>
    <p>
        In order to run an ABParser, you first need to make an instance of it - so, just call the default constructor (which is blank) for your parser, and that's it! <br><br>
        Then, in order to run it - you first call the <code>SetText</code> method on it, passing in the text you want to parse. This is also an <code>ABParserText</code>.<br><br>
        <b>You should try only set the text if it's different from what it was before, it's bad for performance and there's no reason to constantly re-call it with the same string repeatedly.</b><br><br>
        Once you've changed the text, just start the parser using the "Start" method! In the example below, we're using the <code>DemoParser</code> mentioned above.<br><br>
    </p>

    <pre><code class="language-csharp">
using (var parser = new DemoParser()) {
    parser.SetText(new ABParserText("This Is Aa Strbing!"));
    parser.Start();
}
    </code></pre>

    <div class="msgBox warningBox">
		<h4 class="noAnchor">Remember</h4>
		<p> We're initializing the parser within a <code>using</code> statement in order to ensure it gets disposed properly - <b>if you do not do this, the unmanaged part of the ABParser will NEVER get disposed</b>, which can result in memory leaks.</p>
	</div>

    <?php include('../../base/pageBodyEnd.html') ?>
</body>
</html>