<!DOCTYPE html>
<html>

<head>
    <title>Double Comma Parser</title>
    <?php include('../../base/pageHeader.html') ?>
</head>

<body class="lightContainer">
    <?php include('../../base/pageBodyStart.html') ?>

    <h1 id="title">Double Comma Parser</h1>
    <hr>
    <p>
        Now, then, let's take everything we've done and put it together in a proper parser, this parser will parse an made-up format called the <b>double comma format</b>, which is described below.<br><br>
        This is a good test format to use as it also shows ABParser can be useful, as you'll notice that there is a "," token AND a ",," token and ABParser is able to detect which one is in the text seamlessly.
    </p>

    <h2 id="doubleComma">Double Comma</h2>
    <hr>
    <p>
        The double comma format is a format that is made up of arrays, and each array can contain a string and an integer.<br><br>
        In order to seperate each item within each array, we use a single comma, and in order to seperate arrays, we use double commas.<br><br>
        For example, the text:
    </p>

    <pre><code class="language">
1,2,,3,4
    </code></pre>

    <p>
        Has two arrays - one contains the items "1" and "2", and the second contains the items "3" and "4" - those double commas seperate the two arrays.<br><br>
        Now, as was previously mentioned, these arrays can each contain either an integer or a string, and a string is text surrounded by either single or double quotes.<br><br>
        Here's an example document in the double comma format:
    </p>

    <pre><code class="language">
123,456,"Hello",789,,34,'abc',56,,4
    </code></pre>

    <p>This is made up of three arrays - each of which have the following items:</p>

    <ul>
        <li>Integer 123, Integer 456, String "Hello" and Integer 789</li>
        <li>Integer 34, String "abc", Integer 56</li>
        <li>Integer 4</li>
    </ul>

    <h2 id="theResult">The Result</h2>
    <hr>
    <p>So, we will develop a parser that will parse all of these and will output them into a very simple "Result" object, which is shown below. Just look at those briefly so that you have an idea of what we will generate.</p>

    <pre><code class="language-csharp">
public class DoubleCommaResult {
    public List&lt;DoubleCommaArray&gt; Arrays = new List&lt;DoubleCommaArray&gt;();
}

public class DoubleCommaArray {
    public List&lt;DoubleCommaItem&gt; Items = new List&lt;DoubleCommaItem&gt;();
}

public abstract class DoubleCommaItem {}
public class DoubleCommaString : DoubleCommaItem {
    public string Text;

    public DoubleCommaString(string text) => Text = text;
}

public class DoubleCommaInt : DoubleCommaItem {
    public int Num;

    public DoubleCommaInt(int num) => Num = num;
}

    </code></pre>

    <h2 id="makingIt">Making the Parser</h2>
    <h3 id="makingIt-boilerplate">Boilerplate Code</h3>
    <hr>
    <p>Let's start writing some code, so, below is the boilerplate code we need. Just read through that.<br><br>

    <pre><code class="language-csharp">
enum DoubleCommaTokens {
    SingleComma,
    DoubleComma,
    SingleQuote,
    DoubleQuote,
}

public class DoubleCommaParser : ABParser {
    static readonly ABParserConfiguration ParserConfig = ABParserConfiguration.Create(new ABParserToken[]
    {
        new ABParserToken(new ABParserText(nameof(DoubleCommaTokens.SingleComma)), new ABParserText(",")),
        new ABParserToken(new ABParserText(nameof(DoubleCommaTokens.DoubleComma)), new ABParserText(",,")),
        new ABParserToken(new ABParserText(nameof(DoubleCommaTokens.SingleQuote)), new ABParserText("\"")).AddToLimit("DoubleQuoteString"),
        new ABParserToken(new ABParserText(nameof(DoubleCommaTokens.DoubleQuote)), new ABParserText("'")).AddToLimit("SingleQuoteString")
    });

    public DoubleCommaParser() : base(ParserConfig) { }

    public DoubleCommaResult Result;

    protected override void OnStart() {
        Result = new DoubleCommaResult();

        // This is explained below.
        if (!string.IsNullOrWhiteSpace(Text.AsString()))
            AddArray();
    }

    void AddArray() {
        Result.Arrays.Add(new DoubleCommaArray());
    }
}
    </code></pre>
    
    <div class="msgBox infoBox">
		<h4 class="noAnchor">Tip</h4>
		<p>As you can see, a better way of handling token names is to make an enum, since you'll be referring to that name at lot, this way you can change a token's name easily with just refactoring! </p>
	</div>

    <p>
        Now, you'll notice within the <code>OnStart</code> we're adding an array, provided the text isn't empty (or isn't just random whitespace).<br><br>
        This is important because there is no double comma at the beginning. So, we're doing this just for the beginning to make sure that <i>provided there is something there</i> we create that first array!<br><br>
    </p>

    <h3 id="makingIt-boilerplate">Adding Arrays</h3>
    <hr>
    <p>So, that's all of the boilerplate basic code, now let's put in the real code! So, first, if we encounter a double comma token, then we'll add a new array to the result. Now, as you can see, there is already an <code>AddArray</code> method, so we can just call it. We'll add this to the end of our <code>OnTokenProcessed</code>:</p>

    <pre><code class="language-csharp">
protected override void OnTokenProcessed(OnTokenProcessedEventArgs args) {
    switch (args.Token.TokenName) {

        // Add an array when we encounter a double-comma.
        case nameof(DoubleCommaTokens.DoubleComma):
            AddArray();
            break;
    }
}
    </code></pre>

    <p>That's all we need, now, anytime we hit a double comma token, we add a new array in.</p>

    <h3 id="makingIt-boilerplate">Adding Items</h3>
    <h4>Detecting Items</h4>
    <hr>
    <p>
        Next, let's handle items. So, the best way to detect if we've just finished an item is based on the single comma after it. So, everytime we encounter a single comma, we'll just parse the leading. We'll look at parsing the item in a moment.<br><br>
        Now, this is a bit trickier because the last item of an array doesn't have a comma after it! So, whenever we hit a double comma, we need to check if there was an item before it, and, if so, we need to add it.<br><br>
        In addition to that, at the very end we also need to handle the item that came before, so we'll also do exactly the same thing we did with the double comma at the very end. This is how we'll adjust our switch to support this.
    </p>

    <pre><code class="language-csharp">
// Within OnTokenProcessed...
case nameof(DoubleCommaTokens.SingleComma):
    HandleItem(args.Leading.AsString());
    break;
case nameof(DoubleCommaTokens.DoubleComma):
    HandleItemIfNotWhiteSpace(args.Leading.AsString());

    AddArray();
    break;
}


// Outside...
protected override void OnEnd(ABParserText leading) {
    HandleItemIfNotWhiteSpace(leading.AsString());
}

void HandleItemIfNotWhiteSpace(string leading) {
    // Now, there might not have been an item before this, it might've just been blank, for example for the double comma it might've been ",, ,,".
    // The best way to check if there was an item before this is whether there's anything in the "Leading".
    if (!string.IsNullOrWhiteSpace(leading))
        HandleItem(leading);
}
void HandleItem(string leading) { }
    </code></pre>

    <div class="msgBox infoBox">
		<h4 class="noAnchor">Info</h4>
		<p>It is perfectly fine to call "AsString" multiple times as when the object has been converted to a string once, that converted string is stored internally inside the <code>ABParserText</code> as a cache. </p>
	</div>

    <p>That will now detect when there was an item, make sure to look at the comments. Next, we need to create the code that parses those items (in the <code>HandleItem</code> method).</p>

    <h4>Parsing Items</h4>
    <hr>
    <p>
        Now, we have two types of items: A string and an integer.<br><br>
        Here's what will happen when we encounter each of them:
    </p>

    <ul>
        <li>String - These will be handled when we encounter the string tokens, so, when we hit the "comma" that will have already been "parsed", all we need to do is just add that item to the correct array.</li>
        <li>Integer - These don't contain any tokens, so, we will need to "parse" them when we encounter the comma that comes after them.</li>
    </ul>

    <h5>Integers</h5>
    <hr>
    <p>So, the simplest way of parsing an integer, is to literally just call "int.Parse". Then, after we've parsed, all we need to do is generate a <code>DoubleCommaInt</code> object, and add that the array we're currently on (which is of course the last array in the <code>Result</code>).</p>

    <pre><code class="language-csharp">
var num = int.Parse(leading);
itemToAdd = new DoubleCommaInt(num);

Result.Arrays.Last().Items.Add(itemToAdd);
    </code></pre>

    <div class="msgBox infoBox">
		<h4 class="noAnchor">Improvement</h4>
		<p>To improve this, we should really use <code>TryParse</code> and make it throw some kind of exception for an invalid double comma document, but to keep this example simple, we aren't.</p>
	</div>

    <h5>Strings</h5>
    <hr>
    <p>
        OK! Now, as was said above, strings are handled when we encounter the string quote token, so, we'll have a whole seperate part on that.<br><br>
        But, what we will worry about right now is that when the <code>HandleItem</code> method does get called, we need to add that handled string to the current array.<br><br>
        When we make the string handling part, we'll make it put the parsed string into a <code>CurrentStringItem</code> variable, and, we'll also make it set a variable called <code>CurrentItemIsString</code> to true if it found and parsed a string.<br><br>
        Here's the final method:
    </p>

    <pre><code class="language-csharp">
DoubleCommaItem itemToAdd;

if (CurrentItemIsString) {
    itemToAdd = CurrentStringItem;
    CurrentItemIsString = false;
} else {
    var num = int.Parse(leading);
    itemToAdd = new DoubleCommaInt(num);
}
    
Result.Arrays.Last().Items.Add(itemToAdd);
    </code></pre>

    <h2>Strings</h2>
    <h3>Main Handling</h3>
    <hr>
    <p>So, in addition to adding the two variables we mentioned above, we'll also add a boolean that tells us whether we're currently in a string at our current position, we'll call this <code>InString</code>, here's what they all look like together:</p>

    <pre><code class="language-csharp">
DoubleCommaString CurrentStringItem;
bool CurrentItemIsString;
bool InString;
    </code></pre>

    <p>Then, we'll add some very basic code to the <code>OnTokenProcessed</code> that toggles the <code>InString</code> on and off as we hit the quotes. And, when we reach the ending token, we simply set all the values correctly.</p>

    <pre><code class="language-csharp">
case nameof(DoubleCommaTokens.SingleQuote):
case nameof(DoubleCommaTokens.DoubleQuote):

    if (InString) {
        CurrentStringItem = new DoubleCommaString(args.Leading.AsString());
        CurrentItemIsString = true;
    }

    InString = !InString;
    break;
    </code></pre>

    <p>So, what will now happen after the ending quote is the <code>HandleItem</code> will detect that we've already parsed the string and will actually go ahead and add it to the array.</p>

    <h3>TokenLimit</h3>
    <hr>
    <p>
        Next, we need to put in a TokenLimit, that way anything that <i>isn't</i> another string quote doesn't get detected while we're in the string.<br><br>
        If it started with a single quote, we only want to listen out for another single quote, and if it started with a double quote, only for a double quote. So, within the <code>BeforeTokenProcessed</code>, we'll simply put this in:
    </p>

    <pre><code class="language-csharp">
protected override void BeforeTokenProcessed(BeforeTokenProcessedEventArgs args) {

    // Don't set the token limit AGAIN on the second quote!
    if (InString) return;

    switch (args.Token.TokenName) {
        case nameof(DoubleCommaTokens.SingleQuote):
            SetTokenLimit(nameof(DoubleCommaLimits.SingleQuoteString));
            break;
        case nameof(DoubleCommaTokens.DoubleQuote):
            SetTokenLimit(nameof(DoubleCommaLimits.DoubleQuoteString));
            break;
    }
}
    </code></pre>

    <h2>Finished</h2>
    <hr>
    <pre><code class="language-csharp">
public class DoubleCommaParser : ABParser {
    static readonly ABParserConfiguration ParserConfig = ABParserConfiguration.Create(new ABParserToken[]
    {
        new ABParserToken(new ABParserText(nameof(DoubleCommaTokens.SingleComma)), new ABParserText(",")),
        new ABParserToken(new ABParserText(nameof(DoubleCommaTokens.DoubleComma)), new ABParserText(",,")),
        new ABParserToken(new ABParserText(nameof(DoubleCommaTokens.SingleQuote)), new ABParserText("\"")).AddToLimit(nameof("DoubleQuoteString")),
        new ABParserToken(new ABParserText(nameof(DoubleCommaTokens.DoubleQuote)), new ABParserText("'")).AddToLimit(nameof("SingleQuoteString"))
    });
    public DoubleCommaParser() : base(ParserConfig) { }

    public DoubleCommaResult Result;

    DoubleCommaString CurrentStringItem;
    bool CurrentItemIsString;
    bool InString;

    protected override void OnStart() {
        Result = new DoubleCommaResult();

        // Add one array at the beginning if there is actual text within this double comma document.
        if (!string.IsNullOrWhiteSpace(Text.AsString()))
            AddArray();
    }

    protected override void OnTokenProcessed(OnTokenProcessedEventArgs args) {
        switch (args.Token.TokenName) {
            case nameof(DoubleCommaTokens.SingleComma):
                HandleItem(args.Leading.AsString());
                break;
            case nameof(DoubleCommaTokens.DoubleComma):
                if (!string.IsNullOrWhiteSpace(args.Leading.AsString()))
                    HandleItem(args.Leading.AsString());

                AddArray();
                break;
            case nameof(DoubleCommaTokens.SingleQuote):
            case nameof(DoubleCommaTokens.DoubleQuote):

                if (InString) {
                    CurrentStringItem = new DoubleCommaString(args.Leading.AsString());
                    CurrentItemIsString = true;
                }

                InString = !InString;
                break;
        }
    }

    protected override void BeforeTokenProcessed(BeforeTokenProcessedEventArgs args) {
        // Don't set the token limit AGAIN on the second quote!
        if (InString) return;

        switch (args.Token.TokenName) {
            case nameof(DoubleCommaTokens.SingleQuote):
                EnterTokenLimit("SingleQuoteString", true);
                break;
            case nameof(DoubleCommaTokens.DoubleQuote):
                EnterTokenLimit("DoubleQuoteString", true);
                break;
        }
    }
    
    void AddArray() {
        Result.Arrays.Add(new DoubleCommaArray());
    }

    void HandleItem(string leading) {
        DoubleCommaItem itemToAdd;

        if (CurrentItemIsString) {
            itemToAdd = CurrentStringItem;
            CurrentItemIsString = false;
        } else {
            var num = int.Parse(leading);
            itemToAdd = new DoubleCommaInt(num);
        }
            
        Result.Arrays.Last().Items.Add(itemToAdd);
    }
}
    </code></pre>

    <p>
        That's the entire double comma parser right there! That would've taken <i>a lot</i> more effort without ABParser.<br><br>
        Not only that, but as you'll find out in the next, final page, this already has \ escaping and lots of other little optimizations right out-of-the-box!<br><br>
        You'll also, most importantly, find out how to set proper, accurate tokens because in some very rare cases vague tokens can cause strange result.
    </p>

    <?php include('../../base/pageBodyEnd.html') ?>
</body>
</html>