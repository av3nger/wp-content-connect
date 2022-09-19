/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { BaseControl, Button, CheckboxControl, TextControl } from '@wordpress/components';
import { Fragment, useState } from '@wordpress/element';

// Create the component to be rendered in the document settings panel.
const RelationshipsSelectPanel = () => {
	const relationships = window.ContentConnectData.relationships;

	// Generate a default "unselected" state for all relationship items.
	const selected = {};
	relationships.forEach( ( el ) => {
		selected[ el.name ] = false;
	} );

	// State.
	const [ options, setOptions ] = useState( selected );
	const [ panel, togglePanel ] = useState( false );
	const [ name, setName ] = useState( '' );

	// Create selected relationships.
	const createRelationships = () => {
		apiFetch( {
			path: '/content-connect/v1/create-relationships',
			method: 'POST',
			data: {
				nonce: window.ContentConnectData.nonces.api,
				object_type: window.ContentConnectData.relationships[0].object_type,
				post_type: window.ContentConnectData.relationships[0].post_type,
				current_post_id: window.ContentConnectData.relationships[0].current_post_id,
				options,
				name,
				relationships
			},
		} ).then( ( response ) => {
			console.log( response );
		} );
	}

	return (
		<PluginDocumentSettingPanel
			name="tenup-content-connect"
			title={ __( 'Relationships', 'tenup-content-connect' ) }
			className="tenup-content-connect"
		>
			{ relationships.map( ( el ) => {
				return <CheckboxControl
					label={ el.labels.name }
					checked={ options[ el.name ] }
					onChange={ () => setOptions( { ...options, [ el.name ]: ! options[ el.name ] } ) }
				/>;
			} ) }

			<Button
				variant="link"
				className="editor-post-taxonomies__hierarchical-terms-add"
				onClick={ () => togglePanel( ! panel ) }
			>
				{ __( 'Add New Relationship', 'tenup-content-connect' ) }
			</Button>

			{ panel &&
				<Fragment>
					<BaseControl
						className="editor-post-taxonomies__hierarchical-terms-input"
						help={ __( 'Used as a title for the new relationship', 'tenup-content-connect' ) }
					>
						<TextControl
							id="relationship-name-field"
							label={ __( 'New Relationship Name' ) }
							placeholder={ __( 'Draft post', 'tenup-content-connect' ) }
							value={ name }
							onChange={ ( value ) => setName( value ) }
						/>
					</BaseControl>
					<Button
						variant="secondary"
						onClick={ createRelationships }
					>
						{ __( 'Add New Relationship', 'tenup-content-connect' ) }
					</Button>
				</Fragment>
			}
		</PluginDocumentSettingPanel>
	);
};

// Register the plugin.
registerPlugin('tenup-content-connect', {
	render: RelationshipsSelectPanel,
	icon: '',
});
