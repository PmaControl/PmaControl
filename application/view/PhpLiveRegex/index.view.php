<div class="container">
    <div class="row well well-sm" style="padding-bottom: 18px">
        <div class="col-md-6">
            <label>Regex</label>
            <div class="input-group">
                <span class="input-group-addon">/</span>
                <input id="regex_1" type="text" class="form-control" aria-label="regex" value="(.*), (.*)">
                <span class="input-group-addon">/</span>
            </div>
        </div>
        <div class="col-sm-2">
            <label>Regex Options</label>
            <input id="regex_2" class="form-control" id="options" value="" type="text">
        </div>
        <div class="col-sm-4">
            <label>Replacement</label>
            <input id="replacement" class="form-control" id="replacement" value="$0 --&gt; $2 $1" type="text">
        </div>
    </div>
    <div class="row well well-sm">
        <div class="col-md-5">
            <label>Your search string(s)</label>
            <textarea id="examples" class="form-control" rows="20">last_name, first_name
bjorge, philip
kardashian, kim
mercury, freddie</textarea>


        </div>
        <div class="col-md-7">
            <div role="tabpanel">

                <ul class="nav nav-pills" role="tablist">
                    <li role="presentation" class="active"><a href="#preg-match" aria-controls="preg-match" role="tab" data-toggle="pill">preg_match</a></li>
                    <li role="presentation"><a href="#preg-match-all" aria-controls="preg-match-all" role="tab" data-toggle="pill">preg_match_all</a></li>
                    <li role="presentation"><a href="#preg-replace" aria-controls="preg-replace" role="tab" data-toggle="pill">preg_replace</a></li>
                    <li role="presentation"><a href="#preg-grep" aria-controls="preg-grep" role="tab" data-toggle="pill">preg_grep</a></li>
                    <li role="presentation"><a href="#preg-split" aria-controls="preg-split" role="tab" data-toggle="pill">preg_split</a></li>
                </ul>

                <div class="tab-content" style="padding-top: 10px;">
                    <div role="tabpanel" class="tab-pane active" id="preg-match">
                        <input class="form-control" type="text" placeholder="Readonly input here…" readonly>

                    </div>
                    <div role="tabpanel" class="tab-pane" id="preg-match-all"></div>
                    <div role="tabpanel" class="tab-pane" id="preg-replace"></div>
                    <div role="tabpanel" class="tab-pane" id="preg-grep"></div>
                    <div role="tabpanel" class="tab-pane" id="preg-split"></div>
                </div>

            </div>
        </div>
    </div>
    <div class="row well well-sm">
        <div class="col-md-12">
            <strong>Cheat Sheet</strong>
            <br />
            <table style="width:100%">
                <tr>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td><code>[abc]</code></td>
                                    <td>A single character of: a, b or c</td>
                                </tr>
                                <tr>
                                    <td><code>[^abc]</code></td>
                                    <td>Any single character except: a, b, or c</td>
                                </tr>
                                <tr>
                                    <td><code>[a-z]</code></td>
                                    <td>Any single character in the range a-z</td>
                                </tr>
                                <tr>
                                    <td><code>[a-zA-Z]</code></td>
                                    <td>Any single character in the range a-z or A-Z</td>
                                </tr>
                                <tr>
                                    <td><code>^</code></td>
                                    <td>Start of line</td>
                                </tr>
                                <tr>
                                    <td><code>$</code></td>
                                    <td>End of line</td>
                                </tr>
                                <tr>
                                    <td><code>\A</code></td>
                                    <td>Start of string</td>
                                </tr>
                                <tr>
                                    <td><code>\z</code></td>
                                    <td>End of string</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td><code>.</code></td>
                                    <td>Any single character</td>
                                </tr>
                                <tr>
                                    <td><code>\s</code></td>
                                    <td>Any whitespace character</td>
                                </tr>
                                <tr>
                                    <td><code>\S</code></td>
                                    <td>Any non-whitespace character</td>
                                </tr>
                                <tr>
                                    <td><code>\d</code></td>
                                    <td>Any digit</td>
                                </tr>
                                <tr>
                                    <td><code>\D</code></td>
                                    <td>Any non-digit</td>
                                </tr>
                                <tr>
                                    <td><code>\w</code></td>
                                    <td>Any word character (letter, number, underscore)</td>
                                </tr>
                                <tr>
                                    <td><code>\W</code></td>
                                    <td>Any non-word character</td>
                                </tr>
                                <tr>
                                    <td><code>\b</code></td>
                                    <td>Any word boundary</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td><code>(...)</code></td>
                                    <td>Capture everything enclosed</td>
                                </tr>
                                <tr>
                                    <td><code>(a|b)</code></td>
                                    <td>a or b</td>
                                </tr>
                                <tr>
                                    <td><code>a?</code></td>
                                    <td>Zero or one of a</td>
                                </tr>
                                <tr>
                                    <td><code>a*</code></td>
                                    <td>Zero or more of a</td>
                                </tr>
                                <tr>
                                    <td><code>a+</code></td>
                                    <td>One or more of a</td>
                                </tr>
                                <tr>
                                    <td><code>a{3}</code></td>
                                    <td>Exactly 3 of a</td>
                                </tr>
                                <tr>
                                    <td><code>a{3,}</code></td>
                                    <td>3 or more of a</td>
                                </tr>
                                <tr>
                                    <td><code>a{3,6}</code></td>
                                    <td>Between 3 and 6 of a</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
            </table>
            <br>
            <strong>Options</strong>
            <br />
            <div id="regex_options">
                <p>
                    <span style="display: inline-block"><code>i</code> case insensitive&emsp;</span>
                    <span style="display: inline-block"><code>m</code> treat as multi-line string&emsp;</span>
                    <span style="display: inline-block"><code>s</code> dot matches newline&emsp;</span>
                    <span style="display: inline-block"><code>x</code> ignore whitespace in regex&emsp;</span>
                    <span style="display: inline-block"><code>A</code> matches only at the start of string&emsp;</span>
                    <span style="display: inline-block"><code>D</code> matches only at the end of string&emsp;</span>
                    <span style="display: inline-block"><code>U</code> non-greedy matching by default</span>
                </p>
            </div>
        </div>
    </div>
</div>