import { Box, Button, ButtonProps, FormControl, FormControlLabel, FormLabel, makeStyles, Radio, RadioGroup, TextField, Theme } from '@material-ui/core';
import * as React from 'react';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import castmemberhttp from '../../util/http/cast-member-http';

const useStyles = makeStyles((theme:Theme) => {
    return {
        _submit: {
            margin:theme.spacing(1)
        }
    }
});

export const Form = () => {

    const classes = useStyles();

    const buttonProps: ButtonProps = {
        className: classes._submit, 
        variant: "outlined"
    }
    
    const {register, handleSubmit, getValues, setValue} = useForm();

    useEffect( () => {
        register({name: "type"})
    }, [register]);

    const handleChange = (e) => setValue("type", e.target.value);

    function onSubmit(formData, event){       
        castmemberhttp
        .create(formData)
        .then((response) => console.log(response));
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField
                name = "name"
                label = "nome"
                fullWidth
                variant={"outlined"}
                inputRef={register}
                margin={"normal"}
            />
            <FormControl  margin={"normal"}>         
                <FormLabel component="legend">Tipo</FormLabel>   
                <RadioGroup 
                    name="type" 
                    onChange = {(e) => {
                        setValue('type', parseInt(e.target.value))
                    }}>
                    <FormControlLabel 
                        value="1"
                        control={ <Radio /> }
                        label="Diretor"
                    />
                    <FormControlLabel 
                        value="2"
                        control={ <Radio /> }
                        label="Ator"                       
                    />
                </RadioGroup>
            </FormControl>
            <Box dir="rtl">
                <Button {...buttonProps } onClick={() => onSubmit(getValues(), null)} >Salvar</Button>
                <Button {...buttonProps }>Salvar e continuar editando</Button>
            </Box>
        </form>
    )
}