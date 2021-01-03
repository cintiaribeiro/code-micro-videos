import { MenuItem } from '@material-ui/core';
import { Box, Button, ButtonProps, FormControl, FormControlLabel, FormLabel, makeStyles, Radio, RadioGroup, TextField, Theme } from '@material-ui/core';
import { watch } from 'fs';
import * as React from 'react';
import { useState } from 'react';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import castmemberhttp from '../../util/http/cast-member-http';
import genrehttp from '../../util/http/genre-http';

const useStyles = makeStyles((theme:Theme)=>{
    return {
        submit: {
            margin: theme.spacing(1)
        },        
    }
});

export const Form = () => {

    const classes = useStyles();

    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "outlined",        
    };

    const [categories, setCategories] = useState<any[]>([]);
    
    const {register, handleSubmit, getValues, setValue, watch} = useForm({
        defaultValues: {
            category_id: [] as any,
            is_active: true
        }
    });

    useEffect(() => {
        register({name: "category_id"})
    }, [register]);

    useEffect(() => {
        castmemberhttp
        .list()
        .then(({data}) => setCategories(data.data))
    }, []);

    function onSubmit(formData, event) {
        console.log(formData);
        genrehttp
            .create(formData)
            .then((response) => console.log(response));
    }
     
    return(
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField
                name="name"
                label="Nome"
                fullWidth
                variant={"outlined"}
                margin={"normal"}
                inputRef={register}
            />
            <TextField
                select
                name="category_id"
                value={watch('category_id')}
                label="Categorias"
                margin={'normal'}
                variant={'outlined'}
                fullWidth
                onChange={(e)=>{
                    setValue('category_id', e.target.value)
                }}
           
                SelectProps={{
                    multiple: true 
                }}
            >
                <MenuItem value="" disabled>
                    <em>Selecione Categorias</em>
                </MenuItem>
                    {
                        categories.map(
                            (category, key) => (
                                <MenuItem key={key} value={category.id}>{category.name}</MenuItem>
                            )
                        )
                    }
               
            </TextField>

            <Box dir="rtl">
                <Button {...buttonProps } onClick={() => onSubmit(getValues(), null)} >Salvar</Button>
                <Button {...buttonProps }>Salvar e continuar editando</Button>
            </Box>
            
        </form>
    )
}