import { TextField, Checkbox, Box, Button, ButtonProps, makeStyles, Theme} from '@material-ui/core';
import * as React from 'react';
import { useForm } from 'react-hook-form';
import categoryhttp from '../../util/http/category-http';

const useStyles = makeStyles((theme:Theme)=>{
    return {
        _submit: {
            margin: theme.spacing(1)
        },
        get submit() {
            return this._submit;
        },
        set submit(value) {
            this._submit = value;
        },
    }
});

export const Form = () => {
    const classes = useStyles();
    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "outlined",        
    };

    const {register, handleSubmit} = useForm()
    function onSubmit(formData) {
        categoryhttp
            .create(formData)
            .then((response) => console.log(response));
    }
    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField
                inputRef={register}
                name="name"
                label="Nome"
                fullWidth
                variant={"outlined"}
            />
            <TextField
                inputRef={register}
                name="description"
                label="Descrição"
                multiline
                rows="4"
                fullWidth
                variant={"outlined"}
                margin={"normal"}
            />
            <Checkbox
                inputRef={register}
                name="is_active"                
            />
            Ativo?
            <Box dir={"rtl"}>
                <Button {...buttonProps} >Salvar</Button>
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
                
            </Box>
        </form>
    )
}