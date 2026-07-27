Editorial software can look like a collection of forms until a real decision moves through it. Someone identifies a story, another person accepts responsibility, a draft arrives, a reviewer asks for changes, and the final material becomes eligible for delivery. If those transitions are represented only by editable fields, the system loses the reasoning that made the workflow trustworthy.

Razbudise is an editorial workflow and digital publishing platform in active development. Its most useful engineering evidence is not the number of screens. It is the explicit treatment of conversation decisions, assignments, ownership, approval and rejection, traceability, and draft-only delivery.

> **Key takeaway**
>
> Workflow state should record an editorial decision, not merely approximate one from the latest database values.

## Begin with verbs, not tables

I start workflow design by writing the actions people take: propose, assign, accept, submit, approve, reject, deliver. Those verbs expose product rules more clearly than an initial database diagram.

For each action, I ask:

- Who may perform it?
- Which state must already exist?
- What information is required?
- What state follows?
- What evidence must remain?
- Can the action be retried safely?

The answers create a transition model. Persistence can then support that model instead of defining it accidentally. A status column is still useful, but it is no longer expected to explain every decision by itself.

This process also identifies missing concepts. If a draft can be rejected, the product needs to decide whether rejection returns it to the same owner, closes the assignment, or requires a new submission. If reassignment is allowed, the system needs to distinguish a new owner from a modified display field. Ambiguity discovered on paper is cheaper than ambiguity discovered in production data.

## Conversation decisions deserve durable meaning

Editorial work often begins in discussion. A conversation can end without action, or it can produce a decision that creates work. Treating every message as an assignment would create noise; treating the final decision as informal text would make it hard to enforce.

Razbudise separates the conversation from the resulting operation. The decision becomes an explicit event in the workflow, with authorization and validation appropriate to its effect. That creates a stable boundary between discussion and assigned responsibility.

The system does not need to expose private conversation content publicly to demonstrate this design. The public evidence is the existence of a defined decision path and the tests around its allowed outcomes. Confidential editorial material remains outside the portfolio.

## Ownership must be more than a label

An owner is not simply a name shown beside an article. Ownership affects who can submit work, who receives the next action, and how responsibility is understood when the assignment changes.

That means ownership changes should travel through an application operation, not a general record editor. The operation can confirm that the actor has permission, validate the proposed assignee, update the authoritative state, and preserve the transition. The interface then becomes a client of that rule rather than the only place where it exists.

This is also a security concern. A frontend can prevent most accidental misuse, but only the backend can reject a direct request consistently. Server-side enforcement protects the workflow across the administrative interface, API clients, commands, and future integrations.

## Approval and rejection are not booleans

A single `approved` flag appears simple, but it compresses several meanings. Was the work submitted? Who reviewed it? Was it rejected before and resubmitted? Does approval allow delivery, or does another publication decision remain?

Razbudise keeps approval and rejection as explicit transitions. The current state determines which action is valid, and the operation records the result with the required context. This prevents contradictory combinations such as an item appearing approved before it was submitted.

Rejection also needs product care. It should not erase the draft or pretend the assignment never existed. The system should preserve the decision while allowing the next permitted action. That supports traceability without turning the interface into an immutable archive that editors cannot work with.

## Traceability without surveillance

Traceability is useful when it explains responsibility and system behavior. It becomes harmful when every incidental interaction is collected without a product reason.

For Razbudise, the focus is on meaningful state changes: the operation, the actor permitted to perform it, the affected assignment or draft, and the resulting state. That is enough to answer operational questions without treating routine browsing as editorial evidence.

Data minimization applies to logs too. Application logs should identify failed operations and technical context, but they should not copy full unpublished drafts or private discussion into long-lived files. Security review must consider what becomes visible through diagnostics, not only through web routes.

## Make invalid states difficult to represent

The backend should reject transitions that do not make sense. A draft cannot be approved before submission. A closed assignment should not accept a new submission through an old path. An actor without the required role cannot turn a hidden interface action into a valid API request.

These rules can live in focused application services, policies, validators, and database constraints, depending on the invariant. The exact class structure matters less than having one authoritative answer. Duplicated transition rules in controllers, commands, and clients will eventually diverge.

Tests should cover both the successful path and the boundaries around it. A positive test proves that the intended workflow is possible. Negative tests prove that the application refuses shortcuts, stale state, and unauthorized actions.

## Concurrency is a workflow concern

Editorial work is collaborative, so two people may act on the same item. A reviewer could approve a submission while an editor attempts to replace it. An assignment might be changed while a command is preparing a delivery.

The system should not assume that the state displayed when a page loaded is still authoritative when the request arrives. Transaction boundaries, current-state checks, and appropriate locking or version checks help prevent the later request from silently overwriting an earlier decision.

The product response matters too. A conflict should not become a generic success message. The caller needs to know that the underlying state changed and that the operation should be reviewed again.

## Notifications follow authoritative changes

Notifications are useful only when they describe a completed transition. Sending a message before the state change commits can tell someone that work exists when the transaction actually failed.

A safer pattern is to perform the authoritative operation first and trigger follow-up work from the committed result. If notification delivery is asynchronous, retries must avoid producing uncontrolled duplicates. Failure to send a notification should be observable without rolling back an editorial decision that already succeeded.

This separates business truth from communication. The assignment remains assigned even if an email provider is temporarily unavailable. Operations can retry the notification without recreating the decision.

## Draft delivery preserves human control

Razbudise keeps delivery draft-only. Approval inside the editorial workflow does not silently publish to the public site. Delivery prepares content on the destination side while preserving a final review boundary.

This is deliberately conservative. Formatting, links, metadata, image attribution, and source conversion may still need inspection after transfer. A technically successful API request does not prove that the rendered article is editorially ready.

Draft-only behavior is also easier to roll back. A malformed result can be removed or replaced before it becomes a public artifact. The system can improve migration and delivery tooling without treating automation as editorial authority.

## Product interfaces need shared rules

Razbudise includes web and command-line paths. Both can affect content state, so they must share the same domain rules. A command should not bypass authorization or transition checks simply because an operator runs it outside the browser.

Commands need clear inputs, exit codes, idempotent or safely repeatable behavior, and error messages that do not expose secrets. API operations need stable responses and authenticated boundaries. The administrative interface should explain states in human language while delegating decisions to the backend.

This consistency is a maturity test. When a rule exists only in one button handler, the product has an interface convention rather than a domain rule.

## What I would do differently today

I would introduce a concise state-transition document before implementing the first editorial screen. It would list states, permitted actions, actors, and side effects. That artifact would become a shared reference for product decisions, backend code, interface language, and tests.

I would also define a standard conflict response early. Collaborative workflows inevitably encounter stale state, and treating those cases consistently improves both API behavior and editorial clarity.

Finally, I would separate notification policy from workflow implementation from the beginning. The workflow should expose authoritative events; communication channels should subscribe to them without becoming part of the core decision.

## The engineering lesson

Editorial workflow design is an exercise in making responsibility visible. The system should show what decision occurred, who was allowed to make it, which state followed, and what remains possible.

Razbudise is still developing, and its public evidence stays within that boundary. It does not claim automated publishing scale or undisclosed editorial outcomes. It demonstrates a more durable idea: when ownership and transitions are explicit, the product becomes easier to test, secure, operate, and explain.
