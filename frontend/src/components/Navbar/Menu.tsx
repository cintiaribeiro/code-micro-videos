import * as React from 'react';
import { IconButton, Menu as MuiMenu, MenuItem,  } from '@material-ui/core';
import  MenuICon  from '@material-ui/icons/Menu';

export const Menu = () => {

    const [anchorEl, setAnchorEl] = React.useState(null);
    const open = Boolean(anchorEl);
    const handleOpen = (event: any) => setAnchorEl(event.currentTarget);
    const handleClose = () => setAnchorEl(null);

    return (
        <React.Fragment>
            <IconButton
                    edge="start"
                    color="inherit"
                    aria-label="open drawer"
                    aria-aria-controls="menu-appbar"
                    aria-haspopup="true"
                    onClick={handleOpen}
                >
                    <MenuICon/>                    
                </IconButton>
                <MuiMenu
                    open={open}
                    id="menu-appbar"
                    anchorEl={anchorEl}
                    onClick={handleClose}
                    anchorOrigin={{vertical: 'bottom', horizontal: 'center'}}
                    transformOrigin={{vertical: 'top', horizontal: 'center'}}
                    getContentAnchorEl={null}
                > 
                    <MenuItem onClick={handleClose}>
                        Categorias
                    </MenuItem>
                </MuiMenu>
            </React.Fragment>
    );
};