/**
 * The contact form (HANDOFF, section 8): checked on Send — the error under the field in terracotta,
 * aria-invalid and aria-describedby on the field, the focus on the first field with an error. Then it goes to
 * the site (admin-post.php) without leaving the page, and the thank-you line comes up (role="status").
 * Without this script the form posts as usual and the page reloads with the same line.
 */

const form = document.querySelector( '[data-contact-form]' );

if ( form ) {
	const status = form.querySelector( '[data-contact-status]' );
	const button = form.querySelector( 'button[type="submit"]' );
	const fields = [ ...form.querySelectorAll( '[data-required]' ) ];
	const looksLikeEmail = ( value ) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value );

	const check = () => {
		let first = null;
		fields.forEach( ( field ) => {
			const value = field.value.trim();
			const bad = field.dataset.required === 'email' ? ! looksLikeEmail( value ) : value === '';
			field.setAttribute( 'aria-invalid', String( bad ) );
			form.querySelector( `#${ field.getAttribute( 'aria-describedby' ) }` ).hidden = ! bad;
			if ( bad && ! first ) {
				first = field;
			}
		} );
		return first;
	};

	// Once a field has been marked, it clears as soon as it is right.
	fields.forEach( ( field ) =>
		field.addEventListener( 'input', () => {
			if ( field.getAttribute( 'aria-invalid' ) === 'true' ) {
				check();
			}
		} )
	);

	form.addEventListener( 'submit', async ( event ) => {
		event.preventDefault();
		status.textContent = '';
		const first = check();
		if ( first ) {
			first.focus();
			return;
		}
		button.disabled = true;
		try {
			// getAttribute: the hidden field named "action" hides the form's own action property.
			const response = await fetch( form.getAttribute( 'action' ), {
				method: 'POST',
				body: new FormData( form ),
				headers: { Accept: 'application/json' },
				credentials: 'same-origin',
			} );
			const result = await response.json();
			if ( result.ok ) {
				form.reset();
				status.textContent = button.dataset.sent;
			} else {
				status.textContent = button.dataset.failed;
			}
		} catch {
			status.textContent = button.dataset.failed;
		} finally {
			button.disabled = false;
		}
	} );
}
