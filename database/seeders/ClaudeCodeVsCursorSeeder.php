<?php

namespace Database\Seeders;

use App\Actions\SyncComparisonScores;
use App\Enums\ArticleStatus;
use App\Enums\EditorMode;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Criterion;
use App\Models\Tag;
use App\Models\Tool;
use App\Models\ToolCriterion;
use App\Support\ReadingTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClaudeCodeVsCursorSeeder extends Seeder
{
    private const ARTICLE_SLUG = 'claude-code-vs-cursor';

    private const ARTICLE_TITLE = 'Claude Code vs Cursor (2026): The Honest Comparison';

    private const ARTICLE_EXCERPT = 'Claude Code and Cursor are the two AI coding tools most developers weigh in 2026 — one a terminal agent, the other an AI IDE. We compare models, agent features, pricing after the X Subscription change, real token costs, and where each tool actually fits.';

    private const ARTICLE_BODY = <<<'HTML'
<p style="margin:0 0 18px; font-size:17px; color:#334155;">Claude Code and Cursor are the two AI coding tools most developers are choosing between in 2026 — and the comparison has changed shape because both products started converging. Cursor, once known for autocomplete, now ships parallel cloud agents, a full CLI, and worktree orchestration. Claude Code, once a terminal script, now runs as a desktop app, on the web, on mobile, in Slack, and as an IDE extension.</p>

<p style="margin:0 0 18px; font-size:17px; color:#334155;">The short version: <b>Claude Code is built for handing work to an agent; Cursor is built for working alongside one inside an editor.</b> But pricing got more complicated in October 2026, benchmark gaps are smaller than headlines suggest, and each tool now overlaps with the other more than its marketing admits. This comparison is based on official documentation and pricing pages checked in October 2026, independent benchmarks, and recurring patterns from real developer discussions.</p>

<div class="ex-nav-row" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:10px; margin:0 0 20px;">
<a href="#verdict" class="ex-nav-btn" style="display:block; background:#f1f5f9; text-align:center; text-decoration:none; color:#334155;">⚡ The short answer</a>
<a href="#models" class="ex-nav-btn" style="display:block; background:#ecfeff; text-align:center; text-decoration:none; color:#155e75;">🧠 Models</a>
<a href="#editor" class="ex-nav-btn" style="display:block; background:#f0fdf4; text-align:center; text-decoration:none; color:#166534;">⌨️ Editor vs terminal</a>
<a href="#agents" class="ex-nav-btn" style="display:block; background:#f5f3ff; text-align:center; text-decoration:none; color:#5b21b6;">🤖 Agent capabilities</a>
<a href="#pricing" class="ex-nav-btn" style="display:block; background:#fff7ed; text-align:center; text-decoration:none; color:#9a3412;">💰 Pricing</a>
<a href="#costs" class="ex-nav-btn" style="display:block; background:#fef2f2; text-align:center; text-decoration:none; color:#991b1b;">📊 Real-world costs</a>
<a href="#use-cases" class="ex-nav-btn" style="display:block; background:#f8fafc; text-align:center; text-decoration:none; color:#334155;">🎯 Which to choose</a>
<a href="#faq" class="ex-nav-btn" style="display:block; background:#f1f5f9; text-align:center; text-decoration:none; color:#334155;">❓ FAQ</a>
</div>

<h2 id="verdict" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">⚡ The short answer</h2>

<div class="ex-summary-bg">
<div class="ex-grid">
<div class="ex-card ex-card-cyan" style="margin:0;">
<div style="font-weight:800; font-size:16px; margin-bottom:8px;">Choose Claude Code if…</div>
<div style="font-size:15px; line-height:1.6;">You want to delegate whole tasks — refactors, bug hunts, features — and review the result rather than type it. You live in the terminal (or CI), you want Anthropic's strongest models (Claude Opus 5.5 is the default on every plan) with a one-million-token context window, and you prefer a flat subscription over metered credits. There is no free tier: Claude Code starts at the $20/month Pro plan.</div>
</div>
<div class="ex-card ex-card-green" style="margin:0;">
<div style="font-weight:800; font-size:16px; margin-bottom:8px;">Choose Cursor if…</div>
<div style="font-size:15px; line-height:1.6;">You want AI woven into a familiar VS Code-style editor: Tab autocomplete that edits multiple files, visual diffs you approve by keystroke, a built-in browser and debug mode. You also want model choice — Grok and Composer included, Claude and GPT available on Premium and above — and a free tier to start. After October 2026, Cursor is sold as part of The X Subscription, which bundles Grok and X Premium with it.</div>
</div>
</div>
<div class="ex-card ex-card-green-strong" style="margin:14px 0 0;">
<b>The honest summary:</b> these tools no longer compete on the same axis. Cursor is the better place to <i>write</i> code with AI beside you. Claude Code is the better place to <i>hand off</i> work and get a tested change back. They converge — Cursor has a CLI, Claude Code has an editor extension that runs inside Cursor — so many developers in 2026 run both. The decision comes down to whether your bottleneck is keystrokes or delegation, and how predictable you need your bill to be.
</div>
</div>

[[comparison:COMPARISON_ID]]

<h2 id="models" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">🧠 Models: one vendor vs every vendor</h2>

<p>Model access is the deepest structural difference between the two tools, and it drives most of the pricing and performance discussion that follows.</p>

<div class="ex-grid">
<div class="ex-card ex-card-cyan">
<div style="font-weight:800; margin-bottom:8px;">Claude Code: Anthropic models only</div>
<div style="font-size:15px; line-height:1.6;">Claude Code runs Claude models and nothing else. Since v2.1.280 (September 22, 2026), <b>Claude Opus 5.5 is the default on every plan</b> — Pro and Team Standard included — at $4/$20 per million input/output tokens, with a native one-million-token context window. Sonnet 5.5 ($2/$10) and Haiku 5.5 ($0.10/$0.50) cover lighter work, and Fable 5.1 ($10/$50), the most capable model in the lineup, is available as an opt-in for the hardest tasks. For how Claude's models hold up against OpenAI's as general assistants, see our <a href="/articles/chatgpt-vs-claude">ChatGPT vs Claude comparison</a>. You can also run Claude Code through Amazon Bedrock, Google Cloud, or Microsoft Foundry, but the models stay Anthropic's.</div>
</div>
<div class="ex-card ex-card-orange">
<div style="font-weight:800; margin-bottom:8px;">Cursor: model-agnostic, with a catch</div>
<div style="font-size:15px; line-height:1.6;">Cursor's included usage pool covers its first-party models — <b>Grok 4.7, Grok 4.6, Grok 4.5, and Composer 2.5</b>. Its Auto router can pick between GPT-5.5, Claude Opus 5, Grok 4.6, and Claude Fable 5 depending on your mode. The catch: third-party models like Claude and GPT are only available on Premium ($30/month) and above, are not included in the plan pool, and bill at API list prices from prepaid credit. Default context is 200K tokens; Max Mode is limited to legacy plans.</div>
</div>
</div>

<div class="ex-card ex-card-bordered">
<div style="font-weight:700; font-size:16px; margin-bottom:10px;">📊 What the benchmarks actually show</div>
<div style="font-size:15px; line-height:1.6;">On the Vals AI SWE-bench Verified leaderboard (archived September 1, 2026, after the benchmark saturated), <b>Claude Opus 5 leads at 97.0%</b>, followed by GPT-5.6 Sol at 96.2% and Grok 4.6 at 95.6%. Cursor's first-party Composer 2.5, measured through the Cursor CLI harness, sits at 79.6%. Read this carefully: the leaderboard mostly measures models under specific harnesses, not the tools themselves — and Cursor lets you run Opus 5 too, just at API prices. The model-quality gap between the tools is real when both use their defaults, but it narrows to whatever you're willing to pay for in Cursor.</div>
</div>

<h2 id="editor" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">⌨️ The editor question: Tab vs terminal</h2>

<p>The daily-use difference comes down to where the AI sits. Cursor's strongest moat is <b>Tab</b> — a proprietary autocomplete model that suggests multi-line edits, fills in missing imports, predicts your next editing location, and even jumps to another file when a change ripples across the codebase. It is the feature people cite when they say they could not go back, and Claude Code has no equivalent: as of mid-2026, inline tab completion in its IDE extensions is still an open feature request on Anthropic's GitHub.</p>

<p>Claude Code answers with a different workflow. You describe an outcome, and it plans before it touches files, makes edits you can review as diffs, runs commands and tests, and checkpoints the session so you can rewind mistakes. Its permission system got a structural change in September 2026: <b>auto mode is now the default</b>, where a classifier evaluates each action instead of prompting you to approve every command — it blocks destructive operations like <code style="background:#f1f5f9; padding:1px 5px; border-radius:4px; font-size:14px;">git reset --hard</code> unless you explicitly asked for them.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green">
<div style="font-weight:700; margin-bottom:8px;">Where Cursor is stronger</div>
<div style="font-size:15px; line-height:1.6;">Keystroke-level work: completion, inline edits, visual diff review, the built-in browser and debug mode. If the file in front of you is the task and you want to stay in the driver's seat, Cursor's loop is tighter. Its Agents Window (introduced with Cursor 3 in April 2026) adds parallel agent panes for when a task grows beyond one file.</div>
</div>
<div class="ex-card ex-card-violet">
<div style="font-weight:700; margin-bottom:8px;">Where Claude Code is stronger</div>
<div style="font-size:15px; line-height:1.6;">Open-ended tasks: "find why this leaks," "migrate this API across the repo," "implement this spec and open a PR." Claude Code fetches context as it needs it instead of front-loading everything, which independent testing links to dramatically lower token use for multi-file work (see costs below). Its IDE extension — for VS Code, JetBrains, and Cursor itself — adds plan review, @-mentions, and diff viewing when you do want a visual layer.</div>
</div>
</div>

<div class="ex-card ex-card-neutral">
<b>A workflow framing worth stealing:</b> Cursor fits prompts about <i>the code you are looking at</i>; Claude Code fits prompts about <i>the behavior you want</i>. The community crossover is common in 2026 — the Claude Code extension running inside Cursor is a documented setup, and Cursor's CLI (<code style="background:#f1f5f9; padding:1px 5px; border-radius:4px; font-size:14px;">agent</code>) shares worktrees, rules, and MCP servers with the editor, so the two tools are no longer either/or for many teams.
</div>

<h2 id="agents" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">🤖 Agent capabilities: parallelism on both sides</h2>

<p>Both tools now take the same bet — many agents running at once — but they express it differently.</p>

<div class="ex-grid">
<div class="ex-card ex-card-cyan">
<div style="font-weight:800; margin-bottom:8px;">Claude Code's stack</div>
<div style="font-size:15px; line-height:1.6; margin-bottom:10px;">Subagents run in the background by default and can nest three levels deep, with up to twenty running at once. <code style="background:#f1f5f9; padding:1px 5px; border-radius:4px; font-size:14px;">/fork</code> copies a session into a background run with its own worktree; <code style="background:#f1f5f9; padding:1px 5px; border-radius:4px; font-size:14px;">claude agents</code> opens a single dashboard for every live session. Beyond that: dynamic workflows that orchestrate tens to hundreds of agents from a JavaScript script, cross-session messaging between sessions on the same machine, agent teams (experimental, off by default), projects that fan out cloud threads from one conversation, and a background <code style="background:#f1f5f9; padding:1px 5px; border-radius:4px; font-size:14px;">/code-review</code> agent that can comment on your PR. All of it also runs in GitHub Actions and GitLab CI.</div>
</div>
<div class="ex-card ex-card-orange">
<div style="font-weight:800; margin-bottom:8px;">Cursor's stack</div>
<div style="font-size:15px; line-height:1.6; margin-bottom:10px;">The Agents Window runs multiple agents side by side across local folders, worktrees, cloud machines, and SSH hosts. Individual agents offload to the cloud with a single <code style="background:#f1f5f9; padding:1px 5px; border-radius:4px; font-size:14px;">&amp;</code> and keep working after you close the laptop. Automations trigger agents on schedule or via API; best-of-n fans a prompt across multiple parallel attempts; Bugbot reviews pull requests; Origin hosts the code. The CLI shares worktrees and MCP configuration with the editor, so terminal and GUI sessions don't fork your setup.</div>
</div>
</div>

<div class="ex-card ex-card-bordered">
<b>Honest state of play:</b> Cursor 3's Agents Window launch in spring 2026 was rocky — worktree bugs and regressions were reported heavily on Cursor's own forums in April and May, and while the major issues were addressed over the following releases, the feature still draws "not ready for daily use" reports from some teams running it at scale. Claude Code's parallel stack is older and more battle-tested in one respect — it grew out of a terminal tool that always ran long jobs — but it is also more configuration-heavy: background sessions, permission rules, and auto mode all have learning curves of their own.
</div>

<h2 id="pricing" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">💰 Pricing: the October 2026 shake-up</h2>

<p>Before October 12, 2026, this section was simple: Cursor Pro and Claude Pro were both $20/month. That comparison is dead. <b>xAI — now SpaceXSI — acquired Cursor in August 2026, and in October it folded Cursor into The X Subscription</b>, one plan bundling Grok, Grok Bot, Cursor, and X Premium. Existing subscribers can keep their old plans; new customers start on the new ones, and switching is permanent.</p>

<div class="ex-grid">
<div class="ex-card ex-card-cyan">
<div style="font-weight:800; margin-bottom:8px;">Claude plans (Claude Code included)</div>
<div class="ex-inner ex-inner-cyan" style="line-height:1.8;">
<b>Pro</b> — $20/month ($17/month billed annually)<br>
<b>Max 5x</b> — $100/month<br>
<b>Max 20x</b> — $200/month<br>
<b>Team Standard</b> — $25/seat ($20 annual)<br>
<b>Team Premium</b> — $125/seat ($100 annual)<br>
<b>Enterprise</b> — custom
</div>
<div style="font-size:14px; line-height:1.6; margin-top:10px;">Claude Code is included in every paid plan. Usage runs on a five-hour session limit plus a weekly limit, shared across Claude apps and Claude Code; when you hit a limit, optional usage credits kick in at API rates. API pricing has improved twice this year: Claude Code's five-hour rate limits doubled in May 2026, and weekly limits got a permanent 25% increase on September 14. There is no free Claude Code tier.</div>
</div>
<div class="ex-card ex-card-orange">
<div style="font-weight:800; margin-bottom:8px;">Cursor plans (The X Subscription)</div>
<div class="ex-inner ex-inner-orange" style="line-height:1.8;">
<b>Free</b> — $0, limited daily usage<br>
<b>Plus</b> — $8/month<br>
<b>Premium</b> — $30/month (4x Plus usage)<br>
<b>Super</b> — $100/month<br>
<b>Ultra</b> — $200/month<br>
<b>Teams</b> — $30 / $100 / $200 per seat
</div>
<div style="font-size:14px; line-height:1.6; margin-top:10px;">One weekly usage pool is shared across Grok and Cursor, with each tier providing at least twice the coding usage of the Cursor plan it replaces. Third-party models (Claude, GPT) are still billed at API list prices from prepaid credit on Premium and above. Plus limits you to first-party models with fixed settings and no cloud agents. Legacy Cursor plans — Pro at $20, Pro+ at $60 — remain available if you choose not to switch.</div>
</div>
</div>

<div class="ex-card ex-card-bordered">
<div style="font-weight:700; font-size:16px; margin-bottom:12px;">💵 Sticker price by tier (USD/month)</div>
<svg viewBox="0 0 760 250" role="img" aria-label="Bar chart comparing monthly prices: Cursor Free 0 dollars, Cursor Plus 8 dollars, Claude Pro 20 dollars, Cursor Premium 30 dollars, Claude Max 5x and Cursor Super 100 dollars, Claude Max 20x and Cursor Ultra 200 dollars" style="width:100%; height:auto;">
<text x="0" y="14" font-size="13" fill="#64748b" font-weight="600">Cursor Free</text>
<rect x="230" y="3" width="4" height="16" rx="2" fill="#10b981"/>
<text x="244" y="15" font-size="13" fill="#334155" font-weight="700">$0</text>
<text x="0" y="44" font-size="13" fill="#64748b" font-weight="600">Cursor Plus (first-party models)</text>
<rect x="230" y="33" width="33" height="16" rx="2" fill="#10b981"/>
<text x="273" y="45" font-size="13" fill="#334155" font-weight="700">$8</text>
<text x="0" y="74" font-size="13" fill="#64748b" font-weight="600">Claude Pro (Claude Code included)</text>
<rect x="230" y="63" width="83" height="16" rx="2" fill="#06b6d4"/>
<text x="323" y="75" font-size="13" fill="#334155" font-weight="700">$20</text>
<text x="0" y="104" font-size="13" fill="#64748b" font-weight="600">Cursor Premium (cloud agents, 3rd-party)</text>
<rect x="230" y="93" width="125" height="16" rx="2" fill="#10b981"/>
<text x="365" y="105" font-size="13" fill="#334155" font-weight="700">$30</text>
<text x="0" y="134" font-size="13" fill="#64748b" font-weight="600">Claude Max 5x / Cursor Super</text>
<rect x="230" y="123" width="417" height="16" rx="2" fill="#8b5cf6"/>
<text x="657" y="135" font-size="13" fill="#334155" font-weight="700">$100</text>
<text x="0" y="164" font-size="13" fill="#64748b" font-weight="600">Claude Max 20x / Cursor Ultra</text>
<rect x="230" y="153" width="500" height="16" rx="2" fill="#f59e0b"/>
<text x="0" y="210" font-size="12" fill="#94a3b8">Scale: full bar = $200/month. Team seats: Claude $25–$125, Cursor $30–$200 per seat. Prices checked October 2026.</text>
</svg>
</div>

<div class="ex-card ex-card-orange">
<b>⚠️ The pricing traps worth knowing:</b>
<div style="font-size:15px; line-height:1.7; margin-top:8px;">
<b>1. "Using Claude in Cursor" is two bills.</b> The plan pool covers Grok and Composer only — every Claude or GPT request in Cursor draws from prepaid credit at API list prices, on top of your subscription.<br>
<b>2. Cursor's free tier is real but shallow</b> — daily limits, and Plus at $8 still locks you to first-party models at fixed settings.<br>
<b>3. Claude Code has no free tier</b> — you need the $20 Pro plan or API credits from day one.<br>
<b>4. Hitting Claude's session limits doesn't stop you</b> — usage credits continue at API rates, which is exactly how a $20 month becomes a $200 one if you're not watching.
</div>
</div>

<h2 id="costs" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">📊 Real-world costs: token efficiency and bills</h2>

<p>The most-cited number in this debate is a single public benchmark from developer Ian Nuttall, surfaced by Builder.io: building the same Next.js app with Tailwind 4 and shadcn components, <b>Claude Code on Opus finished in 33K tokens with no errors, while Cursor's agent on GPT-5 burned 188K tokens and hit errors along the way</b> — a 5.5x gap.</p>

<div class="ex-card ex-card-bordered">
<div style="font-weight:700; font-size:16px; margin-bottom:12px;">Tokens consumed on one identical build task</div>
<svg viewBox="0 0 760 130" role="img" aria-label="Bar chart: Claude Code used 33 thousand tokens, Cursor agent used 188 thousand tokens on the same task" style="width:100%; height:auto;">
<text x="0" y="20" font-size="14" fill="#64748b" font-weight="600">Claude Code (Opus)</text>
<rect x="230" y="6" width="70" height="20" rx="3" fill="#06b6d4"/>
<text x="310" y="21" font-size="14" fill="#334155" font-weight="700">33K — completed, no errors</text>
<text x="0" y="60" font-size="14" fill="#64748b" font-weight="600">Cursor Agent (GPT-5)</text>
<rect x="230" y="46" width="400" height="20" rx="3" fill="#10b981"/>
<text x="640" y="61" font-size="14" fill="#334155" font-weight="700">188K</text>
<text x="0" y="110" font-size="12" fill="#94a3b8">One task, one run. The ratio belongs to this task — not to every workflow.</text>
</svg>
</div>

<p>Treat that number honestly. It is <b>one task, one run, comparing Claude Code on Opus against Cursor's agent on GPT-5</b> — so it measures harness plus model, not the IDE alone, and it is not a universal 5.5x. What explains the direction is documented: Cursor front-loads context (open files, imports, semantic index matches, git history) while Claude Code fetches context on demand. Front-loading makes answers about the file in front of you feel instant; it gets expensive on a twenty-minute autonomous run. No controlled, multi-task, peer-reviewed token benchmark across identical models exists as of this writing, and neither vendor publishes average tokens per task.</p>

<p>How that translates to bills, on each side:</p>

<div class="ex-grid">
<div class="ex-card ex-card-cyan">
<div style="font-weight:700; margin-bottom:8px;">Claude Code: flat rate, hard ceiling</div>
<div style="font-size:15px; line-height:1.6;">Your subscription covers usage up to the five-hour and weekly limits — Max at $200/month is a genuine ceiling for work that would otherwise be a large API bill. For scale, Anthropic's own documentation cites enterprise API usage averaging around $13 per developer per active day ($150–250 per developer per month), which is the cost the flat tiers replace. The failure mode is running into limits mid-task and letting usage credits at API rates top you up.</div>
</div>
<div class="ex-card ex-card-green">
<div style="font-weight:700; margin-bottom:8px;">Cursor: cheap entry, metered depth</div>
<div style="font-size:15px; line-height:1.6;">Free and $8 entry points are the lowest in the category, and the weekly pool rewards you for staying on first-party models. The reported pain cases are heavy agent usage and premium/third-party models: developers on Cursor's forum and Hacker News have described bills in the hundreds to thousands per month before moving to a flat Claude plan — individual reports, not averages, but a recurring pattern. The same pattern runs the other way: Claude Code users report burning through weekly limits on large batch jobs and paying usage credits.</div>
</div>
</div>

<p><b>Rule of thumb from the pricing structures:</b> predictable budget, heavy autonomous work → Claude's flat tiers. Start free, mix models, keep work light → Cursor. Running Claude inside Cursor as your daily driver → budget for subscription <i>plus</i> API-priced tokens.</p>

<h2 id="privacy" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">🔒 Privacy and security in brief</h2>

<p>Claude Code runs locally on your machine and talks directly to Anthropic's APIs — no remote code index, no middleman server, and auto mode's classifier blocks destructive commands and data-exfiltration patterns before they run. Cursor routes all requests through its own backend (even with your own API keys); with Privacy Mode on, code is never used for training and Cursor holds zero-data-retention agreements with model providers. It carries SOC 2, offers customer-managed encryption keys on Enterprise, and patched two notable 2025 vulnerabilities — the MCP poisoning flaw and a deep-link remote-code-execution bug — within days of disclosure. If your team has a formal security review, Cursor's backend hop is the detail to examine; if your concern is agent autonomy, Claude Code's permission model is the one to read.</p>

<h2 id="use-cases" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">🎯 Which should you choose?</h2>

<div class="ex-grid">
<div class="ex-card ex-card-green">
<div style="font-weight:700; margin-bottom:6px;">You live in the editor, fixing what's in front of you</div>
<div style="font-size:14px; line-height:1.6;"><b>→ Cursor.</b> Tab, visual diffs, browser, debug mode. The tightest possible loop for hands-on work, with a free tier to prove it.</div>
</div>
<div class="ex-card ex-card-cyan">
<div style="font-weight:700; margin-bottom:6px;">You delegate whole tasks and review results</div>
<div style="font-size:14px; line-height:1.6;"><b>→ Claude Code.</b> Plan-first agent, background subagents, worktrees, PR-ready output, strongest default model (Opus 5.5, 1M context).</div>
</div>
<div class="ex-card ex-card-orange">
<div style="font-weight:700; margin-bottom:6px;">You need GPT, Claude, and Grok in one tool</div>
<div style="font-size:14px; line-height:1.6;"><b>→ Cursor Premium.</b> Model choice is its unique advantage — just budget API prices for the third-party models on top of the $30 subscription.</div>
</div>
<div class="ex-card ex-card-violet">
<div style="font-weight:700; margin-bottom:6px;">You run heavy autonomous work on a fixed budget</div>
<div style="font-size:14px; line-height:1.6;"><b>→ Claude Max.</b> A $100–$200 flat ceiling replaces metered API costs — provided the five-hour and weekly limits fit your pace.</div>
</div>
<div class="ex-card ex-card-neutral">
<div style="font-weight:700; margin-bottom:6px;">You're new to AI coding or dislike terminals</div>
<div style="font-size:14px; line-height:1.6;"><b>→ Cursor.</b> A familiar VS Code surface, point-and-click approval of every change, and the only free tier of the two.</div>
</div>
<div class="ex-card ex-card-green-strong">
<div style="font-weight:700; margin-bottom:6px;">You want both worlds</div>
<div style="font-size:14px; line-height:1.6;"><b>→ Run both.</b> The Claude Code extension installs directly in Cursor; Cursor's CLI shares worktrees and MCP config with its editor. This is a documented, supported setup — not a hack — and it's what a growing share of developers actually do.</div>
</div>
</div>

<h2 id="faq" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">❓ Frequently asked questions</h2>

<div class="ex-card ex-card-neutral"><b>Is Claude Code better than Cursor?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Neither is better overall — they optimize for different jobs. Claude Code is stronger for autonomous, multi-file tasks you hand off; Cursor is stronger for hands-on editing with autocomplete and visual diffs. In model benchmarks Claude's default stack leads, but Cursor can run the same Claude models at API prices.</div></div>

<div class="ex-card ex-card-neutral"><b>Can I use Claude Code inside Cursor?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Yes. Claude Code ships an official extension for VS Code, JetBrains, and Cursor, giving you plan review, @-mentions, and diff viewing inside the editor while the agent works. It's one of the most common hybrid setups in 2026 — you need a Claude Pro plan or API credits for it.</div></div>

<div class="ex-card ex-card-neutral"><b>Does Cursor use Claude models?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Yes, but not in the included usage. Claude Opus 5 is available on Cursor Premium ($30/month) and above, billed at API list prices from your prepaid credit. The plan's weekly pool covers only first-party models: Grok 4.7/4.6/4.5 and Composer 2.5.</div></div>

<div class="ex-card ex-card-neutral"><b>Is Claude Code free?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">No. Claude Code is included in paid Claude plans starting at Pro ($20/month) or available through API credits. Anthropic briefly tested moving it off the Pro plan in April 2026 and rolled the change back after community pushback — for now, Pro still includes it, subject to session and weekly usage limits.</div></div>

<div class="ex-card ex-card-neutral"><b>Which is cheaper for heavy daily use?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Usually Claude Code, if you stay within a flat tier: Max at $100–$200/month is a ceiling, whereas Cursor's premium and third-party model usage meters out of prepaid credit on top of the subscription. Light and moderate users often pay less on Cursor — the free and $8 tiers are real, and the weekly pool covers first-party models. Watch both tools' limit resets: usage credits (Claude) and API top-ups (Cursor) are how either bill grows unexpectedly.</div></div>

<div class="ex-card ex-card-neutral"><b>Do I need to know the terminal for Claude Code?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">You need enough comfort to run a command and read output — but not for daily driving. Claude Code also ships a desktop app (with a built-in browser), a web version, mobile apps, and IDE extensions, so the terminal is the most powerful surface rather than the only one. Auto mode now handles most permission decisions for you.</div></div>

<h2 id="bottom-line" style="font-size:19px; font-weight:800; color:#0f172a; margin:26px 0 12px;">🏁 Bottom line</h2>

<div style="background:#0f172a; color:#fff; padding:22px; border-radius:14px;">
<div style="font-size:24px; font-weight:700; margin-bottom:12px;">Delegation vs presence — pick your bottleneck</div>
<div style="font-size:16px; line-height:1.6; opacity:0.95; max-width:720px;">
If your problem is that there isn't enough of you to write every line, <b>Claude Code</b> is the better bet: the strongest default coding model, a mature parallel-agent stack, and a flat-rate ceiling you can plan around. If your problem is friction while you write — completion, context switching, reviewing changes — <b>Cursor</b> is the tighter tool, with model freedom and a free tier no Claude plan matches. The 2026 answer most developers land on isn't a winner: it's Cursor for the keystrokes, Claude Code for the handoffs, and both installed.
</div>
<div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.2); font-size:14px; opacity:0.85;">Pricing, model availability, and limits checked against claude.com, cursor.com, and docs.x.ai in October 2026 — both vendors changed plans this year, so verify before buying.</div>
</div>
HTML;

    public function run(): void
    {
        if (Article::withTrashed()->where('slug->en', self::ARTICLE_SLUG)->exists()) {
            $this->command->info('Article "'.self::ARTICLE_SLUG.'" already exists — skipping.');

            return;
        }

        $aiToolsCategory = Category::where('slug->en', 'ai-tools')->firstOrFail();

        $claudeCode = Tool::firstOrCreate(
            ['slug->en' => 'claude-code'],
            [
                'type' => ToolType::Ai,
                'name' => ['en' => 'Claude Code'],
                'vendor' => 'Anthropic',
                'description' => ['en' => "Anthropic's agentic coding tool: a terminal-first agent (plus VS Code, JetBrains and Cursor extensions, desktop, web and mobile apps) that plans, edits files, runs commands and opens pull requests using Claude models. Included with Claude Pro, Max and Team plans."],
                'status' => ToolStatus::Published,
                'rating_avg' => 8.6,
                'published_at' => now(),
            ],
        );

        $cursor = Tool::firstOrCreate(
            ['slug->en' => 'cursor'],
            [
                'type' => ToolType::Ai,
                'name' => ['en' => 'Cursor'],
                'vendor' => 'SpaceXSI (Anysphere)',
                'description' => ['en' => 'AI code editor built on a VS Code fork with Tab autocomplete, agent chat, cloud agents and a matching CLI. Model-agnostic: Grok and Composer models included in the usage pool, Claude and GPT available at API prices on Premium and above. Sold as part of The X Subscription since October 2026.'],
                'status' => ToolStatus::Published,
                'rating_avg' => 8.4,
                'published_at' => now(),
            ],
        );

        $toolLinks = [
            [$claudeCode, 'claude-code', 'https://claude.com/product/claude-code', 'Try Claude Code'],
            [$cursor, 'cursor', 'https://cursor.com', 'Try Cursor'],
        ];

        foreach ($toolLinks as [$tool, $code, $url, $anchor]) {
            if ($tool->links()->where('code', $code)->exists()) {
                continue;
            }

            $tool->links()->create([
                'code' => $code,
                'url' => $url,
                'anchor' => ['en' => $anchor],
                'is_affiliate' => false,
                'sort_order' => 0,
            ]);
        }

        $criteriaBySlug = [];
        foreach (Criterion::all() as $criterion) {
            $criteriaBySlug[$criterion->getTranslation('name', 'en')] = $criterion;
        }

        $toolValues = [
            $claudeCode->getKey() => [
                'Ease of use' => 7,
                'Features' => 9,
                'Performance' => 9,
                'Pricing' => 7,
                'Support & docs' => 9,
                'Free plan' => false,
                'Open source' => false,
            ],
            $cursor->getKey() => [
                'Ease of use' => 9,
                'Features' => 9,
                'Performance' => 8,
                'Pricing' => 7,
                'Support & docs' => 8,
                'Free plan' => true,
                'Open source' => false,
            ],
        ];

        foreach ($toolValues as $toolId => $values) {
            foreach ($values as $criterionName => $value) {
                $criterion = $criteriaBySlug[$criterionName] ?? null;
                if ($criterion === null) {
                    continue;
                }

                ToolCriterion::firstOrCreate(
                    ['tool_id' => $toolId, 'criteria_id' => $criterion->getKey()],
                    ['value' => $value],
                );
            }
        }

        $tagIds = [];
        foreach (['ai', 'coding'] as $tagName) {
            $tag = Tag::firstOrCreate(
                ['slug->en' => Str::slug($tagName)],
                ['name' => ['en' => $tagName]],
            );
            $tagIds[] = $tag->getKey();
        }

        $article = Article::create([
            'category_id' => $aiToolsCategory->getKey(),
            'title' => ['en' => self::ARTICLE_TITLE],
            'slug' => ['en' => self::ARTICLE_SLUG],
            'excerpt' => ['en' => self::ARTICLE_EXCERPT],
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'editor_mode' => EditorMode::Html,
            'meta_title' => ['en' => 'Claude Code vs Cursor 2026: Which AI Coding Tool Wins?'],
            'meta_description' => ['en' => 'Claude Code vs Cursor compared: models, agent features, 2026 pricing after the X Subscription change, real token costs and which tool fits how you work.'],
        ]);
        $article->tags()->sync($tagIds);

        $comparison = $article->comparisons()->create([
            'title' => ['en' => 'Claude Code vs Cursor: head-to-head'],
            'intro' => ['en' => 'Criteria scores from our testing methodology. Model, feature and pricing data are current as of October 2026.'],
            'verdict' => ['en' => 'Claude Code wins for autonomous, multi-file work and predictable flat-rate pricing; Cursor wins for in-editor assistance, model flexibility and the free entry point. The right pick depends on whether your bottleneck is delegation or keystrokes — and both tools now cross into each other\'s territory.'],
            'sort_order' => 0,
        ]);

        $items = [
            [
                'tool_id' => $claudeCode->getKey(),
                'score' => 8.6,
                'verdict' => 'Delegation-first agent with Anthropic\'s strongest models (Opus 5.5 default, 1M context), mature parallel-agent stack and flat subscriptions. No free tier, no inline autocomplete, Anthropic models only.',
            ],
            [
                'tool_id' => $cursor->getKey(),
                'score' => 8.4,
                'verdict' => 'AI IDE with the best in-editor loop — Tab autocomplete, visual diffs, browser — plus model choice and a free tier. Included pool covers first-party models; Claude and GPT cost API prices on top of the subscription.',
            ],
        ];

        foreach ($items as $position => $data) {
            $comparison->items()->create([
                'tool_id' => $data['tool_id'],
                'position' => $position,
                'score' => $data['score'],
                'verdict' => ['en' => $data['verdict']],
            ]);
        }

        app(SyncComparisonScores::class)->execute($article->refresh());

        $body = str_replace('COMPARISON_ID', (string) $comparison->getKey(), self::ARTICLE_BODY);
        $article->update([
            'body_html' => ['en' => $body],
            'reading_time' => ReadingTime::estimate($body),
        ]);

        $this->command->info('Article "'.self::ARTICLE_SLUG.'" created (comparison #'.$comparison->getKey().').');
    }
}
