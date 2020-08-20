import React from 'react';
// import logo from './logo.svg';
// import {Button} from "@material-ui/core"
import './App.css';
import { Navbar } from './components/Navbar';
import { Page } from './components/Page';
import { Box } from '@material-ui/core';

const App: React.FC = () => {
  return (
    <React.Fragment>
      <Navbar/>
      <Box paddingTop={'70px'}>
        <Page title={'Categorias'}>
          Conteudo aqui
        </Page>
      </Box>    
    </React.Fragment>
    
  );
}

export default App;
