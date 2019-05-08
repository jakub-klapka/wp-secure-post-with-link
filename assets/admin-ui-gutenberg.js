const { registerPlugin } = wp.plugins;
const { PluginPostStatusInfo } = wp.editPost;
const { CheckboxControl } = wp.components;
const { registerStore } = wp.data;

class SecurePostComponent extends React.Component {

    constructor( props ) {
        super( props );

        //TODO: set state more globaly, as this component gets remounted every time panel closes
        //also watcher for post status works only when panel is open
        this.state = {
            secured: true
        };

        this.changeSecuredStatus = this.changeSecuredStatus.bind( this );
    }

    componentDidMount() {

        this.unsubscribe = wp.data.subscribe( () => {

            let changes = wp.data.select( 'core/editor' ).getPostEdits();

            if( this.state.secured === true
                && typeof changes.status !== 'undefined'
                && changes.status !== 'private'
            ) {

                this.changeSecuredStatus( false );
                console.log('Deactivating secured post');

            }

        } );

    }

    componentWillUnmount() {
        this.unsubscribe();
    }

    changeSecuredStatus( is_secured ) {

        if( is_secured === true ) {

            //TODO: change visibility, set meta
            wp.data.dispatch( 'core/editor' ).editPost( { status: 'private' } );

        } else {

            // Unset meta

        }

        this.setState( { secured: is_secured } );
    }

    render() {
        return (
            <CheckboxControl
                label="Secure with secret link"
                checked={ this.state.secured }
                onChange={ this.changeSecuredStatus }
            />
        );
    }

}

class SecurePostWithLinkGutenberg {

    init() {

        registerPlugin( 'secure-post-with-link', {
            render: this.renderPlugin
        } );

    }

    renderPlugin() {

        return(
            <PluginPostStatusInfo>
                <SecurePostComponent/>
            </PluginPostStatusInfo>
        );

    }

}

let instance = new SecurePostWithLinkGutenberg;
instance.init();