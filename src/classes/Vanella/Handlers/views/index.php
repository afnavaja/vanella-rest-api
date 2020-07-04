<!DOCTYPE html><html><head><meta charset="utf-8"><title>Dillinger.md</title><style></style></head><body id="preview">
<h1 class="code-line" data-line-start=0 data-line-end=1><a id="VANELLA_REST_API_0"></a>VANELLA REST API</h1>
<p class="has-line-data" data-line-start="1" data-line-end="2">Vanella REST API is a standalone API framework using native PHP.</p>
<ul>
<li class="has-line-data" data-line-start="3" data-line-end="4">It is very easy to setup.</li>
<li class="has-line-data" data-line-start="4" data-line-end="5">It is reasonably fast because it only uses native PHP and has a very small codebase.</li>
<li class="has-line-data" data-line-start="5" data-line-end="6">Has a built-in command-line interface called vanella to help you get started with your project!</li>
<li class="has-line-data" data-line-start="6" data-line-end="7">It has a built-in create, read, update, delete API functionality.</li>
<li class="has-line-data" data-line-start="7" data-line-end="8">It uses oAuth2 for client app authentication.</li>
<li class="has-line-data" data-line-start="8" data-line-end="9">It uses JWT for generating an access token.</li>
<li class="has-line-data" data-line-start="9" data-line-end="10">You can create your custom API call right off the bat using your favorite vanilla PHP or object-oriented style of coding or via the VANELLA command-line interface!</li>
<li class="has-line-data" data-line-start="10" data-line-end="12">You can also add additional libraries via the composer, depending on your needs.</li>
</ul>
<h3 class="code-line" data-line-start=12 data-line-end=13><a id="Installation_12"></a>Installation</h3>
<ul>
<li class="has-line-data" data-line-start="14" data-line-end="15">Utilizes <a href="https://getcomposer.org/download/">composer</a>.</li>
<li class="has-line-data" data-line-start="15" data-line-end="16">PHP Version (&gt;= 5.3.0, safest in PHP 7).</li>
<li class="has-line-data" data-line-start="16" data-line-end="18">MySQL</li>
</ul>
<pre><code class="has-line-data" data-line-start="19" data-line-end="21" class="language-sh">$ composer create-project afnav/vanella-rest-api
</code></pre>
<h3 class="code-line" data-line-start=22 data-line-end=23><a id="Basic_Configuration_Follow_these_steps_22"></a>Basic Configuration (Follow these steps)</h3>
<ul>
<li class="has-line-data" data-line-start="23" data-line-end="27">Go to the root of your project directory. Run this on your terminal(Linux) or cmd(Windows) to see the list of active commands.<pre><code class="has-line-data" data-line-start="25" data-line-end="27" class="language-sh">$ php vanella
</code></pre>
</li>
<li class="has-line-data" data-line-start="27" data-line-end="31">Run and choose [all] to create the initial config.<pre><code class="has-line-data" data-line-start="29" data-line-end="31" class="language-sh">$ php vanella create:config
</code></pre>
</li>
<li class="has-line-data" data-line-start="31" data-line-end="32">Go to your app root directory /src/config/database.php to change the database credentials.</li>
<li class="has-line-data" data-line-start="32" data-line-end="33">In your /src/config/main.php file, there is an active_env key; you change it to “development” or “production” if you want. The default value is “development”.</li>
</ul>
<h3 class="code-line" data-line-start=36 data-line-end=37><a id="Basic_CLI_Commands_Normal_Process_Flow_36"></a>Basic CLI Commands (Normal Process Flow)</h3>
<ol>
<li class="has-line-data" data-line-start="37" data-line-end="42">
<p class="has-line-data" data-line-start="37" data-line-end="38">Run this command to create your first endpoint group.</p>
<pre><code class="has-line-data" data-line-start="40" data-line-end="42" class="language-sh"> $ php vanella create:endpointgroup
</code></pre>
</li>
<li class="has-line-data" data-line-start="42" data-line-end="43">
<p class="has-line-data" data-line-start="42" data-line-end="43">After that, you can locate your class file in “/src/restful/SomeClassFileForYourEndpointGroup.php”. See examples below.</p>
</li>
</ol>
<ul>
<li class="has-line-data" data-line-start="43" data-line-end="44"><a href="http://yourwebsite.com/users/read">http://yourwebsite.com/users/read</a></li>
<li class="has-line-data" data-line-start="44" data-line-end="45"><a href="http://yourwebsite.com/users/create">http://yourwebsite.com/users/create</a></li>
<li class="has-line-data" data-line-start="45" data-line-end="46"><a href="http://yourwebsite.com/users/update">http://yourwebsite.com/users/update</a></li>
<li class="has-line-data" data-line-start="46" data-line-end="48"><a href="http://yourwebsite.com/users/delete">http://yourwebsite.com/users/delete</a></li>
</ul>
<blockquote>
<p class="has-line-data" data-line-start="48" data-line-end="49">The [read, create, update, delete] are built-in endpoints when you extend to the Vanella\Handlers\Restful class. So you don’t have to recreate these endpoints.</p>
</blockquote>
<h3 class="code-line" data-line-start=50 data-line-end=51><a id="Basic_CLI_Commands_Things_you_need_to_know_before_you_continue_50"></a>Basic CLI Commands (Things you need to know before you continue)</h3>
<ol>
<li class="has-line-data" data-line-start="51" data-line-end="52">“/users” are the Endpoint Group, which is and also should be the name of your class files. In this example, your class file should be named Users.php in your /src/resftul/Users.php directory.</li>
<li class="has-line-data" data-line-start="52" data-line-end="53">The “/read” or “/create” or “/update” or “/delete” are the endpoints. The endpoints are the name of your class functions. Luckily, the Vanella commands are ready to do that for you, so you don’t need to worry! ;)</li>
<li class="has-line-data" data-line-start="53" data-line-end="55">All endpoints groups or endpoint class files are in /src/restful/ directory.</li>
</ol>
<h3 class="code-line" data-line-start=55 data-line-end=56><a id="Basic_CLI_Commands_Creating_Endpoints_55"></a>Basic CLI Commands (Creating Endpoints)</h3>
<ol>
<li class="has-line-data" data-line-start="56" data-line-end="60">Run this command to create an endpoint.<pre><code class="has-line-data" data-line-start="58" data-line-end="60" class="language-sh"> $ php vanella create:endpoint
</code></pre>
</li>
<li class="has-line-data" data-line-start="60" data-line-end="61">The app will create those endpoints for you. See the examples below.</li>
</ol>
<ul>
<li class="has-line-data" data-line-start="61" data-line-end="62"><a href="http://yourwebsite.com/users/yourcustomEndpoint">http://yourwebsite.com/users/yourcustomEndpoint</a></li>
<li class="has-line-data" data-line-start="62" data-line-end="63"><a href="http://yourwebsite.com/users/anotherCustomEndpoint">http://yourwebsite.com/users/anotherCustomEndpoint</a></li>
</ul>
<ol start="3">
<li class="has-line-data" data-line-start="63" data-line-end="73">
<p class="has-line-data" data-line-start="63" data-line-end="64">There are five endpoint types that you can choose to generate the code when running create:endpoint which is [basic,read,create,update,delete]</p>
<table class="table table-striped table-bordered">
<thead>
<tr>
<th>TYPE</th>
<th>DESCRIPTION</th>
<th>ACCEPTS METHOD</th>
</tr>
</thead>
<tbody>
<tr>
<td>basic</td>
<td>Only creates an empty function which equals to the desired endpoint. From here, you can run native PHP code to your heart’s content!</td>
<td>N/A</td>
</tr>
<tr>
<td>create</td>
<td>Creates a ready-made endpoint app-generated code to create records to your specified database table.</td>
<td>POST</td>
</tr>
<tr>
<td>read</td>
<td>Creates a ready-made endpoint app-generated code to view your records to your specified database table. And it already has built-in pagination as well.</td>
<td>GET</td>
</tr>
<tr>
<td>update</td>
<td>Creates a ready-made endpoint app-generated code to update records to your specified database table.</td>
<td>POST OR PUT</td>
</tr>
<tr>
<td>delete</td>
<td>Creates a ready-made endpoint app-generated code to delete records to your specified database table.</td>
<td>DELETE</td>
</tr>
</tbody>
</table>
</li>
</ol>
<ul>
<li class="has-line-data" data-line-start="73" data-line-end="74">
<blockquote>
<p class="has-line-data" data-line-start="73" data-line-end="74">Important: The endpoint group class files already extends to Vanella\Handlers\Restful class, so you don’t necessarily have to generate CRUD[create,read,update,delete] API endpoints on your class files.</p>
</blockquote>
</li>
<li class="has-line-data" data-line-start="74" data-line-end="75">
<blockquote>
<p class="has-line-data" data-line-start="74" data-line-end="75">But there may be times that you need to have some minor changes or customization to a specific endpoint, so Vanella REST API helps you generate necessary sourcecode for that to get you started!</p>
</blockquote>
</li>
<li class="has-line-data" data-line-start="75" data-line-end="77">
<blockquote>
<p class="has-line-data" data-line-start="75" data-line-end="76">Again, just use your vanilla PHP to create that sophisticated API process! :p</p>
</blockquote>
</li>
</ul>
<h4 class="code-line" data-line-start=77 data-line-end=78><a id="About_the_endpoint_types_read_update_delete_77"></a>About the endpoint types [read, update, delete]</h4>
<ul>
<li class="has-line-data" data-line-start="78" data-line-end="81">Endpoint type: read creates a ready-made endpoint app-generated code to view your records to your specified database table. And it already has built-in pagination as well. See the examples below. Supposing you already have records on your “users” database table.
<ul>
<li class="has-line-data" data-line-start="79" data-line-end="80"><a href="http://yourwebsite.com/users/read/page/1">http://yourwebsite.com/users/read/page/1</a></li>
<li class="has-line-data" data-line-start="80" data-line-end="81"><a href="http://yourwebsite.com/users/read/page/2">http://yourwebsite.com/users/read/page/2</a></li>
</ul>
</li>
<li class="has-line-data" data-line-start="81" data-line-end="84">Endpoint type: update creates a ready-made endpoint app-generated code to update records to your specified database table.
<ul>
<li class="has-line-data" data-line-start="82" data-line-end="83"><a href="http://yourwebsite.com/users/update/%7Bid%7D">http://yourwebsite.com/users/update/{id}</a></li>
<li class="has-line-data" data-line-start="83" data-line-end="84"><a href="http://yourwebsite.com/users/update/1">http://yourwebsite.com/users/update/1</a></li>
</ul>
</li>
<li class="has-line-data" data-line-start="84" data-line-end="88">Endpoint type: delete creates a ready-made endpoint app-generated code to delete records to your specified database table.
<ul>
<li class="has-line-data" data-line-start="85" data-line-end="86"><a href="http://yourwebsite.com/users/delete/%7Bid%7D">http://yourwebsite.com/users/delete/{id}</a></li>
<li class="has-line-data" data-line-start="86" data-line-end="88"><a href="http://yourwebsite.com/users/delete/1">http://yourwebsite.com/users/delete/1</a></li>
</ul>
</li>
</ul>
<h2 class="code-line" data-line-start=88 data-line-end=89><a id="Activating_Builtin_Authentication_88"></a>Activating Built-in Authentication</h2>
<ol>
<li class="has-line-data" data-line-start="90" data-line-end="94">Run this command to activate authentication. The app will generate the necessary files for authentication.<pre><code class="has-line-data" data-line-start="92" data-line-end="94" class="language-sh"> $ php vanella activate:auth
</code></pre>
</li>
<li class="has-line-data" data-line-start="94" data-line-end="95">Test the auth using Postman. If you don’t have Postman on your computer, download it here! <a href="https://www.postman.com/downloads">https://www.postman.com/downloads</a></li>
<li class="has-line-data" data-line-start="95" data-line-end="96">Primarily uses OAuth2.0 for client authentication to generate the JWT access tokens.</li>
<li class="has-line-data" data-line-start="96" data-line-end="97">The initial configuration can be found in /src/config/authentication.php</li>
<li class="has-line-data" data-line-start="97" data-line-end="99">The authenticated apps configuration can be found in /src/config/authenticatedApps.php</li>
</ol>
<h3 class="code-line" data-line-start=99 data-line-end=100><a id="Conclusion_99"></a>Conclusion</h3>
<p class="has-line-data" data-line-start="100" data-line-end="101">If you have questions, you can reach me through my email at <a href="/cdn-cgi/l/email-protection#eb8a8d858a9d8a818aab8c868a8287c5888486"><span class="__cf_email__" data-cfemail="7e1f18101f081f141f3e19131f1712501d1113">[email&#160;protected]</span></a>!</p>
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script></body></html>