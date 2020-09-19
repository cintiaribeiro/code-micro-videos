import { Chip } from '@material-ui/core';
import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import React , {useState, useEffect}from 'react';
import { httpVideo } from '../../util/http';
import format from "date-fns/format";
import parseISO from 'date-fns/parseISO';

const columsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Nome"
    },
    {
        name: "categories",
        label: "Categorias",
        options:{
            customBodyRender(value, tableMeta, updateValue){
                //console.log(value)
                const itens = value.map((item:any)=> {
                    let categorias:string = '';
                    return categorias += item.name +', ';
                    
                })
                // console.log(itens.substr(0, itens.length -2));
                return itens;
            }
        }
    },
    {
        name: "is_active",
        label: "Ativo?",
        options:{
            customBodyRender(value, tableMeta, updateValue){
                return value ? <Chip label="Sim" color="primary"/> : <Chip label="Não" color="secondary"/>
            }
        }
    },
    {
        name: "created_at",
        label: "Criado em",
        options:{
            customBodyRender(value, tableMeta, updateValue){
            return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }
    }

];


type Props = {

};
const Table = (props: Props) => {

    const [data, setData ] = useState([]);
    
    useEffect(()=>{
        httpVideo.get('genres').then(
            response =>  setData(response.data.data)
        )
    }, []);

    return (
        <div>
            <MUIDataTable 
                title="Listagem de categorias"
                columns={columsDefinition}
                data={data}
            />
        </div>
    );
}

export default Table;