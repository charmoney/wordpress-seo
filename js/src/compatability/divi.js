/* External dependencies */
import forEach from "lodash/forEach";

const DIVI_EDITOR_WRAPPER_ID = "et_pb_main_editor_wrap";
const DIVI_CLASSIC_EDITOR_HIDDEN_CLASS = "et_pb_hidden";

class DiviHelper {
	static isActive() {
		return !! document.getElementById( DIVI_EDITOR_WRAPPER_ID );
	}

	static isTinyMCEHidden() {
		const classicEditorContainer = document.getElementById( DIVI_EDITOR_WRAPPER_ID );
		if( ! classicEditorContainer ) {
			return false;
		}
		return classicEditorContainer.classList.contains( DIVI_CLASSIC_EDITOR_HIDDEN_CLASS );
	}

	/**
	 * Listen for changes to the classic editor when the Divi page builder is active.
	 *
	 * @param {Object}   callbacks                     The listener callbacks.
	 * @param {Function} callbacks.classicEditorHidden Callback called when TinyMCE is hidden.
	 * @param {Function} callbacks.classicEditorShown  Callback called when TinyMCE is shown.
	 *
	 * @returns {void}
	 */
	listen( callbacks ) {
		this.classicEditorContainer = document.getElementById( DIVI_EDITOR_WRAPPER_ID );
		if( ! this.classicEditorContainer ) {
			return;
		}
		const observer = new MutationObserver( mutationsList => {
			forEach( mutationsList, mutation => {
				if( mutation.type === "attributes" && mutation.attributeName === "class" ) {
					if( mutation.target.classList.contains( "et_pb_hidden" ) ) {
						callbacks.classicEditorHidden();
					} else {
						callbacks.classicEditorShown();
					}
				}
			} );
		} );
		observer.observe( this.classicEditorContainer, {
			attributes: true,
		} );
	}
}

export default DiviHelper;